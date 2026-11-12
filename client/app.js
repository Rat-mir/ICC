import {api,session,imageBlob} from './api.js';
import {createComposer,defaultLayers} from './composition.js';
const $=s=>document.querySelector(s);
const app=$('#app');
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const statuses={draft:'Черновик',pending:'На проверке',approved:'Опубликован',revision:'На доработке',rejected:'Отклонён',hidden:'Скрыт'};
const categories={background:'Фон',body:'Основа',clothes:'Одежда',face:'Лицо',hair:'Причёска',accessory:'Аксессуар'};
let user=null, routeId=0, objectUrls=[];
const isAdmin=()=>user?.role==='admin';
const author=account=>esc(account?.name)+(account?.is_creator?' <span class="creator-mark" title="Креатор" aria-label="Креатор">✦</span>':'');
const badge=s=>'<span class="badge '+esc(s)+'">'+esc(statuses[s]||s)+'</span>';
const errors='<div class="form-error" role="alert"></div>';
const field=(name,label,type='text',value='',extra='')=>'<label>'+label+'<input name="'+name+'" type="'+type+'" value="'+esc(value)+'" '+extra+'><span class="field-error" data-field="'+name+'"></span></label>';
const note=s=>{const t=$('#toast');t.textContent=s;t.hidden=false;clearTimeout(note.timer);note.timer=setTimeout(()=>t.hidden=true,4500);};
function header() {
  const page=(location.hash.slice(1)||'catalog').split(/[/?]/)[0];
  const link=(hash,icon,label,pages=[hash])=>'<a href="#'+hash+'" '+(pages.includes(page)?'aria-current="page"':'')+'><span aria-hidden="true">'+icon+'</span>'+label+'</a>';
  $('#nav').innerHTML='<div class="nav-group">'+link('catalog','▦','Мейкеры',['catalog','maker'])+
    link('posts','▤','Сообщество',['posts','post','new-post','edit-post'])+'</div>'+
    (user?'<div class="nav-group"><strong>Личное</strong>'+link('characters','◇','Мои персонажи',['characters','character'])+link('studio','✎','Мастерская',['studio','edit'])+'</div>':'')+
    (isAdmin()?'<div class="nav-group"><strong>Управление</strong>'+link('admin','☷','Заявки',['admin','review'])+link('admin-posts','▤','Публикации')+link('users','♙','Аккаунты')+'</div>':'');
  $('#account').innerHTML=user?'<span class="account-name">'+author(user)+(isAdmin()?' <span class="badge">Администратор</span>':'')+'</span><button class="secondary small" id="logout">Выйти</button>':'<a href="#login">Войти</a><a class="button small" href="#register">Регистрация</a>';
  $('#logout')?.addEventListener('click',async()=>{
    try{await api('/logout',{method:'POST'});}catch(e){note(e.message);}
    session.token=null;user=null;postDraft=null;
    if(location.hash==='#catalog')await render();else location.hash='catalog';
  });
}
function formError(form,e) {
  form.querySelectorAll('.field-error').forEach(n=>n.textContent='');
  const box=form.querySelector('.form-error'); if(box)box.textContent=e.message;
  Object.entries(e.errors||{}).forEach(([key,messages])=>{const el=form.querySelector('[data-field="'+key.split('.')[0]+'"]');if(el)el.textContent=messages.join(' ');});
}
function form(id,handler) { $('#'+id)?.addEventListener('submit',async e=>{e.preventDefault();const f=e.currentTarget;const buttons=[...f.querySelectorAll('button[type=submit]')];buttons.forEach(b=>b.disabled=true);try{await handler(f,new FormData(f));}catch(x){formError(f,x);}finally{buttons.forEach(b=>b.disabled=false);}}); }
function bindActions() { app.querySelectorAll('[data-action]').forEach(b=>b.addEventListener('click',async()=>{b.disabled=true;try{await actions[b.dataset.action]?.(b);}catch(e){note(e.message);}finally{b.disabled=false;}})); }
const actions={
  async removeMaker(b){if(confirm('Удалить черновик мейкера?')){await api('/makers/'+b.dataset.id,{method:'DELETE'});note('Мейкер удалён');render();}},
  async removeCharacter(b){if(confirm('Удалить сохранённого персонажа?')){await api('/characters/'+b.dataset.id,{method:'DELETE'});note('Персонаж удалён');render();}},
  async removePost(b){if(confirm('Удалить публикацию?')){await api('/posts/'+b.dataset.id,{method:'DELETE'});note('Публикация удалена');location.hash='posts';render();}},
  async removeComment(b){if(confirm('Удалить комментарий?')){await api('/comments/'+b.dataset.id,{method:'DELETE'});render();}},
  async like(b){
    const {data}=await api('/posts/'+b.dataset.id+'/like',{method:b.dataset.remove?'DELETE':'PUT'});
    if(data.liked)b.dataset.remove='1';else delete b.dataset.remove;
    b.setAttribute('aria-pressed',String(data.liked));
    b.textContent=(data.liked?'♥ ':'♡ ')+data.likes_count;
  },
  async hideMaker(b){await api('/admin/makers/'+b.dataset.id+'/hide',{method:'POST'});note('Мейкер скрыт');render();},
  async hidePost(b){await api('/admin/posts/'+b.dataset.id+'/hide',{method:'POST'});note('Публикация скрыта');render();},
};
function pagination(meta,route,params) {
  const url=page=>{const p=new URLSearchParams(params);p.set('page',page);return '#'+route+'?'+p;};
  return '<div class="pagination">'+(meta.current_page>1?'<a class="button secondary small" href="'+esc(url(meta.current_page-1))+'">← Назад</a>':'')+'<span class="muted">Страница '+meta.current_page+' из '+meta.last_page+' · всего '+meta.total+'</span>'+(meta.current_page<meta.last_page?'<a class="button secondary small" href="'+esc(url(meta.current_page+1))+'">Далее →</a>':'')+'</div>';
}
async function loadImages() {
  await Promise.all([...app.querySelectorAll('img[data-image]')].map(async img=>{try{const url=await imageBlob(img.dataset.image);objectUrls.push(url);if(img.isConnected)img.src=url;}catch{img.alt='Изображение недоступно';}}));
}
function makerCard(m,studio=false) {
  const href='#'+(studio?'edit':'maker')+'/'+m.id;
  return '<article class="card"><a class="thumb" href="'+href+'" aria-label="'+esc(m.title)+'"><span class="placeholder">◇</span><canvas data-maker-preview="'+m.id+'" width="512" height="512" hidden></canvas></a><div class="card-body"><h3><a href="'+href+'">'+esc(m.title)+'</a></h3><div class="muted">'+author(m.creator)+'</div>'+
    (studio?'<div class="row">'+badge(m.status)+(['draft','revision'].includes(m.status)?'<button class="secondary small" data-action="removeMaker" data-id="'+m.id+'">Удалить</button>':'')+'</div>':'')+'</div></article>';
}

async function previews(makers) {
  const composer=createComposer();
  await Promise.allSettled(makers.map(async maker=>{
    const canvas=app.querySelector('[data-maker-preview="'+maker.id+'"]');
    if(!canvas)return;
    const {data}=await api('/makers/'+maker.id);
    const layers=defaultLayers(data.assets||[]);
    if(!canvas.isConnected||!layers.length)return;
    await composer.draw(canvas,layers,false);
    if(canvas.isConnected){canvas.hidden=false;canvas.previousElementSibling.hidden=true;}
  }));
}

async function catalog(params,studio=false,admin=false) {
  const view=routeId;
  if(studio&&!user || admin&&!isAdmin())return denied();
  const route=studio?'studio':admin?'admin':'catalog';
  const p=new URLSearchParams(params);
  if(studio)p.set('mine','1');
  const result=await api((admin?'/admin':'')+'/makers?'+p);
  if(view!==routeId)return;
  const title=studio?'Мастерская':admin?'Заявки на модерацию':'Мейкеры';
  const heading=studio||admin
    ? '<div class="page-heading row"><h1>'+title+'</h1>'+(studio?'<a class="button" href="#edit/new">Создать мейкер</a>':'')+'</div>'
    : '<section class="page-heading catalog-hero"><div><h1>Interactive Character Catalog</h1><p>Всем peace! Рады видеть вас, на нашем сайте.</p></div><img class="catalog-mascot" src="mascot-peace.png" alt="Маскот ICC показывает жест мира" width="128" height="128"></section>';
  const filterStatuses=admin?Object.entries(statuses).filter(([key])=>key!=='draft'):Object.entries(statuses);
  app.innerHTML=heading+
    '<form id="filters" class="toolbar">'+errors+
    '<label class="search-field">Поиск<input name="search" value="'+esc(p.get('search')||'')+'" placeholder="Название мейкера"></label>'+
    ((studio||admin)?'<label>Статус<select name="status"><option value="">Все статусы</option>'+filterStatuses.map(([v,t])=>'<option value="'+v+'" '+(p.get('status')===v?'selected':'')+'>'+(admin&&v==='approved'?'Одобрен':t)+'</option>').join('')+'</select></label>':'')+
    '<label>Сортировка<select name="sort"><option value="">По умолчанию</option><option value="created_at" '+(p.get('sort')==='created_at'?'selected':'')+'>Сначала новые</option><option value="title" '+(p.get('sort')==='title'?'selected':'')+'>По названию</option></select></label><button type="submit">Найти</button></form>'+
    '<div class="grid '+(admin?'':'maker-grid')+'">'+result.data.map(m=>admin?'<article class="panel"><div class="row">'+(m.status==='approved'?'<span class="badge approved">Одобрен</span>':badge(m.status))+'</div><h3>'+esc(m.title)+'</h3><p>'+author(m.creator)+'</p><a class="button secondary" href="#review/'+m.id+'">Открыть</a></article>':makerCard(m,studio)).join('')+'</div>'+
    (result.data.length?'':'<div class="empty">'+(admin&&!p.get('search')&&!p.get('status')?'Заявок пока нет.':'Мейкеры не найдены.')+'</div>')+pagination(result.meta,route,params);
  form('filters',async(f,d)=>{
    const q=new URLSearchParams;
    for(const [key,value] of d)if(value)q.set(key,value);
    if(q.get('sort')==='title')q.set('order','asc');
    location.hash=route+(q.size?'?'+q:'');
  });
  bindActions();
  if(!admin)previews(result.data);
  if(admin){
    const heading=app.querySelector('.page-heading');
    const {data}=await api('/admin/statistics');
    if(!heading.isConnected)return;
    const box=document.createElement('section');
    box.className='panel';
    box.innerHTML='<h2>Статистика</h2><div class="stats">'+[['Аккаунты',data.users],['Персонажи',data.characters],['Публикации',data.posts],['На проверке',data.makers_by_status.pending||0]].map(([t,n])=>'<div class="stat"><b>'+n+'</b>'+t+'</div>').join('')+'</div>';
    heading.after(box);
  }
}
function denied(){app.innerHTML='<div class="panel"><h1>Нужен доступ</h1><p>Войдите в аккаунт с подходящей ролью.</p><a class="button" href="#login">Войти</a></div>';}
async function authPage(register) {
  app.innerHTML='<section class="panel auth"><h1>'+(register?'Регистрация':'Вход')+'</h1><form id="auth">'+errors+(register?field('name','Ваше имя','text','','required maxlength="100"'):'')+field('email','Электронная почта','email','','required autocomplete="email"')+field('password','Пароль','password','','required minlength="8" maxlength="128" autocomplete="'+(register?'new-password':'current-password')+'"')+(register?field('password_confirmation','Повторите пароль','password','','required minlength="8" autocomplete="new-password"'):'')+'<button type="submit">'+(register?'Зарегистрироваться':'Войти')+'</button></form><p class="hint">'+(register?'Уже есть аккаунт? <a href="#login">Войти</a>':'Нет аккаунта? <a href="#register">Создать</a>')+'</p></section>';
  form('auth',async(f,d)=>{const r=await api(register?'/register':'/login',{method:'POST',body:Object.fromEntries(d)});session.token=r.token;user=r.user;header();note(register?'Аккаунт создан':'Вы вошли в ICC');location.hash='catalog';});
}
async function editMaker(id) {
  const view=routeId;
  if(!user)return denied();
  const m=id==='new'?null:(await api('/makers/'+id)).data;
  if(view!==routeId)return;
  if(m&&(m.vector_style||(!isAdmin()&&m.user_id!==user.id)))return denied();
  app.innerHTML='<a href="#studio">← В мастерскую</a><h1>'+(m?esc(m.title):'Новый мейкер')+'</h1>'+(m?badge(m.status):'')+(m?.review_comment?'<div class="review">'+esc(m.review_comment)+'</div>':'')+'<div class="split"><section class="panel"><h2>О мейкере</h2><form id="maker-form">'+errors+field('title','Название','text',m?.title||'','required maxlength="255"')+'<label>Описание<textarea name="description" required maxlength="5000">'+esc(m?.description||'')+'</textarea><span class="field-error" data-field="description"></span></label><button type="submit" style="margin-top:18px">Сохранить</button></form><p class="hint">Изменение опубликованного набора снимает его с публикации до новой проверки.</p>'+(m?'<form id="submit-maker">'+errors+'<span class="field-error" data-field="assets"></span><button type="submit">Отправить на проверку →</button></form>':'')+'</section><section class="panel"><h2>PNG-слои</h2><p class="muted">До 5 МБ · 16–2048 px · одинаковый размер всех PNG. Для проверки обязательны body и face.</p>'+(m?'<form id="asset-form">'+errors+field('name','Название слоя','text','','required')+'<label>Категория<select name="category">'+Object.entries(categories).map(([k,v])=>'<option value="'+k+'">'+v+' ('+k+')</option>').join('')+'</select><span class="field-error" data-field="category"></span></label>'+field('layer_order','Порядок слоя','number','10','required min="0" max="1000"')+field('image','PNG-файл','file','','required accept="image/png"')+'<button type="submit" style="margin-top:15px">Загрузить PNG</button></form>':'<p>Сначала сохраните мейкер.</p>')+'</section></div>'+(m?'<section class="panel"><h2>Элементы набора</h2><div class="asset-list">'+m.assets.map(a=>'<form class="asset-box" data-asset-form="'+a.id+'"><img data-image="'+esc(a.image_url)+'" alt="'+esc(a.name)+'">'+errors+field('name','Название','text',a.name,'required')+'<label>Категория<select name="category">'+Object.entries(categories).map(([k,v])=>'<option value="'+k+'" '+(k===a.category?'selected':'')+'>'+v+'</option>').join('')+'</select></label>'+field('layer_order','Порядок','number',a.layer_order,'min="0" max="1000"')+'<label><input type="checkbox" name="is_active" '+(a.is_active?'checked':'')+'> Активен</label><div class="actions"><button class="small" type="submit">Обновить</button><button type="button" class="danger small" data-delete-asset="'+a.id+'">Удалить</button></div></form>').join('')+'</div></section>':'');
  form('maker-form',async(f,d)=>{const result=await api('/makers'+(m?'/'+id:''),{method:m?'PATCH':'POST',body:Object.fromEntries(d)});note('Мейкер сохранён');if(m)render();else location.hash='edit/'+result.data.id;});
  form('asset-form',async(f,d)=>{await api('/makers/'+id+'/assets',{method:'POST',body:d});note('PNG загружен');render();});
  form('submit-maker',async()=>{await api('/makers/'+id+'/submit',{method:'POST'});user=(await api('/user')).data;note('Заявка отправлена администратору');render();});
  app.querySelectorAll('[data-asset-form]').forEach(f=>{f.id='asset-'+f.dataset.assetForm;form(f.id,async(form,d)=>{const b=Object.fromEntries(d);b.is_active=form.elements.is_active.checked;await api('/makers/'+id+'/assets/'+f.dataset.assetForm,{method:'PATCH',body:b});note('Слой обновлён');render();});});
  app.querySelectorAll('[data-delete-asset]').forEach(b=>b.onclick=async()=>{if(!confirm('Удалить PNG-элемент?'))return;try{await api('/makers/'+id+'/assets/'+b.dataset.deleteAsset,{method:'DELETE'});render();}catch(e){note(e.message);}});
  loadImages();
}
async function builder(id,character=null) {
  const view=routeId;
  const {data:maker}=await api('/makers/'+id);
  if(view!==routeId)return;
  if(maker.status!=='approved'){
    app.innerHTML='<h1>Мейкер недоступен</h1><p>'+esc(statuses[maker.status])+'</p>';
    return;
  }
  const vector=Boolean(maker.vector_style);
  const labels={...categories,face:vector?'Глаза':'Лицо',body:vector?'Основа':categories.body};
  if(maker.vector_style==='furry')labels.body='Вид и шерсть';
  if(maker.vector_style==='pony')Object.assign(labels,{body:'Масть',hair:'Грива и хвост',clothes:'Украшения',accessory:'Рог и крылья'});
  const assets=maker.assets.filter(asset=>asset.is_active);
  const imported=assets.find(asset=>asset.vector?.dicebear)?.vector.style;
  if(imported)for(const asset of assets)labels[asset.category]=asset.vector.label;
  const credit=makerCredits(maker);
  const groups=Object.keys(labels).filter(category=>assets.some(asset=>asset.category===category));
  let selected=character?character.assets.filter(asset=>assets.some(active=>active.id===asset.id)):defaultLayers(assets);
  let category=groups.includes('hair')?'hair':groups[0];
  const composer=createComposer();
  const back=character?'#characters':'#catalog';
  app.innerHTML='<a class="back-link" href="'+back+'">← '+(character?'Мои персонажи':'Мейкеры')+'</a><div class="page-heading"><h1>'+esc(maker.title)+'</h1><p class="muted">'+esc(maker.description)+'</p>'+credit+'</div>'+
    '<div class="editor"><section class="canvas-wrap"><canvas id="character-canvas" width="512" height="512" aria-label="Предпросмотр персонажа"></canvas><div class="actions"><button class="secondary" id="randomize" type="button">Случайный образ</button><button id="download" type="button">Скачать PNG</button>'+(user?'<button class="secondary" type="button" id="publish-image">Создать публикацию</button>':'')+'</div></section>'+
    '<section class="editor-controls"><div id="part-tabs" class="part-tabs" aria-label="Части образа"></div><div id="part-options" class="part-options"></div>'+
    (!vector?'<details class="layer-details"><summary>Порядок слоёв</summary><div id="layer-order"></div></details>':'')+
    (user?'<form id="character-form">'+errors+field('name','Имя персонажа','text',character?.name||'','required maxlength="255"')+'<span class="field-error" data-field="asset_ids"></span><button type="submit">'+(character?'Сохранить изменения':'Сохранить персонажа')+'</button></form>':'<p class="hint"><a href="#login">Войдите</a>, чтобы сохранить персонажа в аккаунте.</p>')+'</section></div>';
  const canvas=$('#character-canvas');
  const tabs=$('#part-tabs');
  const options=$('#part-options');
  const thumbnails=new Map();
  const required=key=>imported?assets.some(asset=>asset.category===key&&asset.vector.required):vector&&['body','face'].includes(key);
  function paint(){
    return composer.draw(canvas,selected).catch(error=>note(error.message));
  }
  function choose(asset){
    const index=selected.findIndex(item=>item.category===category);
    if(index>=0)selected.splice(index,1,...(asset?[asset]:[]));
    else if(asset)selected.push(asset);
    if(vector||index<0)selected.sort((a,b)=>a.layer_order-b.layer_order);
    controls();
    paint();
  }
  function controls(){
    tabs.innerHTML=groups.map(key=>'<button type="button" class="secondary" data-part="'+key+'" aria-pressed="'+(key===category)+'">'+labels[key]+'</button>').join('');
    tabs.querySelectorAll('[data-part]').forEach(button=>button.onclick=()=>{
      const key=button.dataset.part;
      category=key;
      controls();
      tabs.querySelector('[data-part="'+key+'"]').focus();
    });
    const current=selected.find(asset=>asset.category===category);
    const items=assets.filter(asset=>asset.category===category);
    const furryBody=maker.vector_style==='furry'&&category==='body';
    const variants=new Map();
    for(const asset of items){
      if(furryBody&&asset.vector.coat!==(current?.vector.coat??0))continue;
      const key=asset.vector?.variant??asset.id;
      if(!variants.has(key)||asset.vector?.color===current?.vector?.color)variants.set(key,asset);
    }
    const colorOptions=current?.vector?items.filter(asset=>asset.vector.variant===current.vector.variant):[];
    const coatOptions=furryBody&&current?items.filter(asset=>asset.vector.species===current.vector.species&&asset.vector.color===current.vector.color):[];
    options.innerHTML=(coatOptions.length?'<div class="coat-row" aria-label="Рисунок шерсти">'+coatOptions.map(asset=>'<button type="button" class="secondary" aria-pressed="'+(asset.id===current.id)+'" data-choice="'+asset.id+'">'+['Однотонная','Полосы','Пятна'][asset.vector.coat]+'</button>').join('')+'</div>':'')+
      (colorOptions.length>1?'<div class="swatch-row" aria-label="Цвет">'+colorOptions.map(asset=>'<button type="button" class="swatch" aria-pressed="'+(asset.id===current.id)+'" aria-label="Цвет '+asset.vector.color+'" data-choice="'+asset.id+'" style="--swatch:'+asset.vector.color+'"></button>').join('')+'</div>':'')+
      '<div class="part-grid">'+(!required(category)?'<button type="button" class="part-choice" aria-pressed="'+!current+'" data-choice="">'+(maker.vector_style==='pony'&&category==='accessory'?'Обычный пони':'Без детали')+'</button>':'')+
      [...variants.values()].map(asset=>'<button type="button" class="part-choice" aria-pressed="'+(asset.id===current?.id)+'" data-choice="'+asset.id+'"><img data-image="'+esc(asset.image_url)+'" alt=""><span>'+esc(furryBody?asset.name.split(' · ')[0]:asset.name)+'</span></button>').join('')+'</div>';
    options.querySelectorAll('[data-choice]').forEach(button=>button.onclick=()=>{
      const choice=button.dataset.choice;
      const kind=button.classList.contains('swatch')?'swatch':button.classList.contains('part-choice')?'part-choice':'secondary';
      choose(items.find(asset=>String(asset.id)===choice));
      options.querySelector('.'+kind+'[data-choice="'+choice+'"]')?.focus();
    });
    options.querySelectorAll('img[data-image]').forEach(async img=>{
      const asset=items.find(item=>item.image_url===img.dataset.image);
      try{
        if(!thumbnails.has(asset.id)){
          const src=await composer.thumbnail(asset);
          thumbnails.set(asset.id,src);
        }
        if(img.isConnected)img.src=thumbnails.get(asset.id);
      }catch{img.hidden=true;}
    });
    const order=$('#layer-order');
    if(!order)return;
    order.innerHTML=selected.map((asset,index)=>'<div class="layer-order"><span>'+esc(asset.name)+'</span><span><button type="button" class="secondary small" data-move="'+index+'" data-delta="-1" aria-label="Выше" '+(index===0?'disabled':'')+'>↑</button> <button type="button" class="secondary small" data-move="'+index+'" data-delta="1" aria-label="Ниже" '+(index===selected.length-1?'disabled':'')+'>↓</button></span></div>').join('');
    order.querySelectorAll('[data-move]').forEach(button=>button.onclick=()=>{
      const i=+button.dataset.move,j=i+(+button.dataset.delta);
      [selected[i],selected[j]]=[selected[j],selected[i]];
      controls();
      paint();
    });
  }
  controls();
  await paint();
  if(view!==routeId)return;
  canvas.dataset.ready='true';
  $('#randomize').onclick=()=>{
    selected=groups.map(key=>{
      const items=assets.filter(asset=>asset.category===key);
      return items[Math.floor(Math.random()*items.length)];
    }).sort((a,b)=>a.layer_order-b.layer_order);
    controls();
    paint();
  };
  async function withPNG(button,done){
    button.disabled=true;
    try{await done(await composer.png(selected));}catch(error){note(error.message);}
    finally{button.disabled=false;}
  }
  $('#download').onclick=event=>withPNG(event.currentTarget,blob=>{
    const link=document.createElement('a');
    link.download='icc-character.png';
    link.href=URL.createObjectURL(blob);
    link.click();
    setTimeout(()=>URL.revokeObjectURL(link.href),1000);
  });
  const publish=$('#publish-image');
  if(publish)publish.onclick=()=>withPNG(publish,blob=>{
    postDraft={blob,characterId:character?.id,name:character?.name||''};
    location.hash='new-post';
  });
  form('character-form',async(f,d)=>{
    const r=await api('/characters'+(character?'/'+character.id:''),{method:character?'PUT':'POST',body:{name:d.get('name'),maker_id:maker.id,asset_ids:selected.map(asset=>asset.id)}});
    note('Персонаж сохранён');
    if(character)await render();else location.hash='character/'+r.data.id;
  });
}
async function characters(params) {
  const view=routeId;
  if(!user)return denied();const r=await api('/characters?'+params);
  if(view!==routeId)return;
  app.innerHTML='<h1>Мои персонажи</h1><div class="grid">'+r.data.map(c=>'<article class="panel"><h3>'+esc(c.name)+'</h3><p class="muted">'+esc(c.maker?.title)+'</p><div class="actions"><a class="button" href="#character/'+c.id+'">Открыть</a><button class="danger" data-action="removeCharacter" data-id="'+c.id+'">Удалить</button></div></article>').join('')+'</div>'+(r.data.length?'':'<div class="empty">Создайте первого героя в <a href="#catalog">каталоге мейкеров</a>.</div>')+pagination(r.meta,'characters',params);bindActions();
}
async function posts(params,moderation=false) {
  const view=routeId;
  if(moderation&&!isAdmin())return denied();
  const r=await api((moderation?'/admin':'')+'/posts?'+params);
  if(view!==routeId)return;
  if(!r.data.length&&r.meta.total===0){
    app.innerHTML='<div class="row page-heading"><h1>'+(moderation?'Модерация публикаций':'Сообщество')+'</h1>'+
      (user&&!moderation?'<a class="button" href="#new-post">Создать публикацию</a>':'')+'</div><div class="empty">Публикаций пока нет.</div>';
    return;
  }
  app.innerHTML='<div class="row"><div><h1>'+(moderation?'Модерация публикаций':'Сообщество')+'</h1></div>'+(user?'<a class="button" href="#new-post">Создать публикацию</a>':'')+'</div><div class="grid">'+r.data.map(p=>'<article class="card"><a class="thumb" href="#post/'+p.id+'"><img data-image="'+esc(p.image_url)+'" alt="'+esc(p.title)+'"></a><div class="card-body"><span class="eyebrow">'+author(p.author)+'</span><h3><a href="#post/'+p.id+'">'+esc(p.title)+'</a></h3><div class="muted">♡ '+p.likes_count+' · комментариев '+p.comments_count+(p.is_visible?'':' · Скрыт')+'</div>'+(moderation?'<button class="danger small" data-action="hidePost" data-id="'+p.id+'">Скрыть</button>':'')+'</div></article>').join('')+'</div>'+pagination(r.meta,moderation?'admin-posts':'posts',params);bindActions();loadImages();
}
function actionMenu(items) {
  return items.length?'<details class="action-menu"><summary aria-label="Действия">⋯</summary><div>'+items.join('')+'</div></details>':'';
}
const actionButton=(action,id,label)=>'<button type="button" data-action="'+action+'" data-id="'+id+'">'+label+'</button>';

async function post(id) {
  const view=routeId;
  const {data:p}=await api('/posts/'+id);
  if(view!==routeId)return;
  const manage=user&&(user.id===p.user_id||isAdmin());
  const actions=[];
  if(manage)actions.push('<a href="#edit-post/'+id+'">Редактировать</a>',actionButton('removePost',id,'Удалить'));
  if(isAdmin()&&p.is_visible)actions.push(actionButton('hidePost',id,'Скрыть'));
  app.innerHTML='<a class="back-link" href="#posts">← Сообщество</a><div class="row page-heading"><h1>'+esc(p.title)+'</h1>'+actionMenu(actions)+'</div>'+
    '<div class="split post-layout"><img class="post-image" data-image="'+esc(p.image_url)+'" alt="'+esc(p.title)+'"><div><section class="panel post-meta"><b>'+author(p.author)+'</b>'+
    (p.body?'<p class="post-body">'+esc(p.body)+'</p>':'')+
    '<div class="post-reactions">'+(user&&p.is_visible?'<button class="secondary" type="button" data-action="like" data-id="'+id+'" '+(p.liked?'data-remove="1"':'')+' aria-pressed="'+p.liked+'" aria-label="Нравится">'+(p.liked?'♥':'♡')+' '+p.likes_count+'</button>':'<span>♡ '+p.likes_count+'</span>')+'</div></section>'+
    '<section class="panel discussion"><h2>Комментарии <span class="muted">'+p.comments_count+'</span></h2>'+
    p.comments.map(c=>'<article class="comment"><div class="row"><b>'+author(c.author)+'</b>'+actionMenu(user&&(user.id===c.user_id||isAdmin())?[actionButton('removeComment',c.id,'Удалить')]:[])+'</div><p>'+esc(c.body)+'</p></article>').join('')+
    (user&&p.is_visible?'<form id="comment-form">'+errors+'<label>Комментарий<textarea name="body" required maxlength="2000"></textarea><span class="field-error" data-field="body"></span></label><button type="submit">Отправить</button></form>':!user?'<p><a href="#login">Войдите</a>, чтобы написать комментарий.</p>':'')+'</section></div></div>';
  bindActions();
  loadImages();
  form('comment-form',async(f,d)=>{
    await api('/posts/'+id+'/comments',{method:'POST',body:Object.fromEntries(d)});
    render();
  });
}

let postDraft=null;

async function newPost(id) {
  const view=routeId;
  if(!user)return denied();
  const existing=id?(await api('/posts/'+id)).data:null;
  if(existing&&existing.user_id!==user.id&&!isAdmin())return denied();
  const draft=id?null:postDraft;
  const chars=id?[]:(await api('/characters?per_page=100')).data.filter(c=>c.user_id===user.id);
  if(view!==routeId)return;
  app.innerHTML='<a class="back-link" href="#'+(id?'post/'+id:'posts')+'">← '+(id?'Публикация':'Сообщество')+'</a><h1>'+(id?'Редактировать публикацию':'Новая публикация')+'</h1>'+
    '<div class="split compose"><section class="panel"><h2>Изображение</h2><img class="post-image" id="post-preview" alt="Предпросмотр" '+(existing?'data-image="'+esc(existing.image_url)+'"':'hidden')+'><div id="post-preview-empty" class="empty" '+(existing?'hidden':'')+'>Выберите изображение</div></section>'+
    '<form class="panel" id="post-form">'+errors+field('title','Название','text',existing?.title||draft?.name||'','required maxlength="255"')+
    '<label>Описание<textarea name="body" maxlength="3000">'+esc(existing?.body||'')+'</textarea><span class="field-error" data-field="body"></span></label>'+
    (id?'':'<label>Источник изображения<select name="source">'+(draft?'<option value="draft">Образ из редактора</option>':'')+
      (chars.length?'<option value="character">Сохранённый персонаж</option>':'')+'<option value="file" '+(!draft?'selected':'')+'>Загрузить PNG</option></select></label>'+
      '<label id="post-character">Персонаж<select name="character_id"><option value="">Выберите персонажа</option>'+chars.map(c=>'<option value="'+c.id+'">'+esc(c.name)+'</option>').join('')+'</select><span class="field-error" data-field="character_id"></span></label>'+
      '<div id="post-upload">'+field('image','PNG до 5 МБ','file','','accept="image/png"')+'</div>')+
    '<div class="actions"><button type="submit">'+(id?'Сохранить':'Опубликовать')+'</button></div></form></div>';
  if(id){
    loadImages();
    form('post-form',async(f,d)=>{
      await api('/posts/'+id,{method:'PATCH',body:Object.fromEntries(d)});
      location.hash='post/'+id;
    });
    return;
  }
  const f=$('#post-form'), preview=$('#post-preview'), empty=$('#post-preview-empty');
  const source=f.elements.source, character=f.elements.character_id, image=f.elements.image;
  const composer=createComposer();
  let previewUrl, revision=0, pending;
  function refresh(){
    const current=++revision;
    $('#post-character').hidden=source.value!=='character';
    $('#post-upload').hidden=source.value!=='file';
    character.required=source.value==='character';
    image.required=source.value==='file';
    preview.hidden=true;
    empty.hidden=false;
    empty.textContent='Выберите изображение';
    if(previewUrl)URL.revokeObjectURL(previewUrl);
    const characterId=source.value==='character'?character.value:source.value==='draft'?draft.characterId:null;
    pending=(async()=>{
      let blob=source.value==='draft'?draft.blob:source.value==='file'?image.files[0]:null;
      if(source.value==='character'&&characterId){
        const {data}=await api('/characters/'+characterId);
        blob=await composer.png(data.assets);
      }
      if(current===revision&&blob&&preview.isConnected){
        previewUrl=URL.createObjectURL(blob);
        objectUrls.push(previewUrl);
        preview.src=previewUrl;
        preview.hidden=false;
        empty.hidden=true;
      }
      return {blob,characterId};
    })();
    pending.catch(error=>{if(current===revision)empty.textContent=error.message;});
  }
  source.onchange=character.onchange=image.onchange=refresh;
  refresh();
  form('post-form',async(form,d)=>{
    const current=revision;
    const {blob,characterId}=await pending;
    if(current!==revision)throw new Error('Источник изменился. Проверьте предпросмотр.');
    if(!blob)throw new Error('Выберите изображение.');
    d.delete('source');
    d.delete('character_id');
    if(characterId)d.set('character_id',characterId);
    d.set('image',blob,'character.png');
    const {data}=await api('/posts',{method:'POST',body:d});
    postDraft=null;
    location.hash='post/'+data.id;
  });
}
function makerCredits(maker) {
  const meta=maker.dicebear_meta;
  if(!meta)return '';
  const link=(title,url)=>/^https?:\/\//i.test(url||'')?'<a href="'+esc(url)+'">'+esc(title)+'</a>':esc(title);
  return '<p class="muted">Адаптация для ICC · '+link(meta.creator.name,meta.creator.url)+' · '+
    link(meta.license.name,meta.license.url)+' · '+link('Оригинальный стиль','https://www.dicebear.com/styles/'+maker.vector_style+'/')+'</p>';
}

async function review(id) {
  const view=routeId;
  if(!isAdmin())return denied();const m=(await api('/makers/'+id)).data;
  if(view!==routeId)return;
  const imported=Boolean(m.dicebear_meta);
  const contents=imported
    ? '<canvas class="post-image" id="review-preview" width="512" height="512" aria-label="Предпросмотр мейкера"></canvas>'+makerCredits(m)
    : '<div class="asset-list">'+m.assets.map(a=>'<div class="asset-box"><img data-image="'+esc(a.image_url)+'" alt="'+esc(a.name)+'"><b>'+esc(a.name)+'</b><p class="muted">'+esc(a.category)+' · слой '+a.layer_order+'</p></div>').join('')+'</div>';
  app.innerHTML='<a href="#admin">← Все заявки</a><div class="row"><h1>'+esc(m.title)+'</h1>'+badge(m.status)+'</div><p>'+esc(m.description)+'</p><p class="muted">Автор: '+esc(m.creator?.name)+'</p><div class="split"><section class="panel"><h2>Содержимое набора</h2>'+contents+'</section><section class="panel"><h2>Решение администратора</h2>'+(m.review_comment?'<div class="review">'+esc(m.review_comment)+'</div>':'')+(m.status==='pending'?'<form id="review-form">'+errors+'<label>Решение<select name="decision"><option value="approve">Одобрить</option>'+(imported?'':'<option value="request-revision">Вернуть на доработку</option>')+'<option value="reject">Отклонить</option></select><span class="field-error" data-field="decision"></span></label><label>Комментарий<textarea name="review_comment" maxlength="2000"></textarea><span class="field-error" data-field="review_comment"></span></label><p class="hint">Причина обязательна при отказе'+(imported?'':' или возврате')+'.</p><button type="submit" style="margin-top:15px">Сохранить решение</button></form>':'<p>'+ (m.status==='approved'?'Одобрен':esc(statuses[m.status]))+'</p>')+(m.status==='approved'?'<div class="actions"><button class="danger" data-action="hideMaker" data-id="'+id+'">Скрыть мейкер</button></div>':'')+'</section></div>';
  form('review-form',async(f,d)=>{await api('/admin/makers/'+id+'/review',{method:'POST',body:Object.fromEntries(d)});note('Решение сохранено');render();});bindActions();loadImages();
  if(imported)createComposer().draw($('#review-preview'),defaultLayers(m.assets)).catch(error=>note(error.message));
}
async function users(params) {
  const view=routeId;
  if(!isAdmin())return denied();const r=await api('/admin/users?'+params);
  if(view!==routeId)return;
  app.innerHTML='<span class="eyebrow">Администрирование</span><h1>Аккаунты и роли</h1><p class="muted">Смена роли и блокировка завершают активные сеансы аккаунта.</p><section class="panel">'+r.data.map(u=>'<form class="user-row" id="user-'+u.id+'">'+errors+'<span><b>'+author(u)+'</b><br><small>'+esc(u.email)+'</small></span><select name="role">'+Object.entries({user:'Пользователь',creator:'Креатор',admin:'Администратор'}).map(([v,t])=>'<option value="'+v+'" '+(v===u.role?'selected':'')+'>'+t+'</option>').join('')+'</select><div><label><input type="checkbox" name="is_blocked" '+(u.is_blocked?'checked':'')+'> Заблокирован</label><button type="submit" class="small" '+(u.id===user.id?'disabled':'')+'>Сохранить</button></div></form>').join('')+'</section>'+pagination(r.meta,'users',params);
  r.data.filter(u=>u.id!==user.id).forEach(u=>form('user-'+u.id,async(f,d)=>{await api('/admin/users/'+u.id,{method:'PATCH',body:{role:d.get('role'),is_blocked:f.elements.is_blocked.checked}});note('Аккаунт обновлён');render();}));
}
async function render() {
  const seq=++routeId;
  objectUrls.forEach(u=>URL.revokeObjectURL(u));objectUrls=[];
  const [path,query='']=(location.hash.slice(1)||'catalog').split('?'),[page,id]=path.split('/');
  app.innerHTML='<p class="muted">Загрузка…</p>';window.scrollTo(0,0);header();
  try {
    if(page==='login'||page==='register')await authPage(page==='register');
    else if(page==='catalog')await catalog(query);
    else if(page==='studio')await catalog(query,true);
    else if(page==='admin')await catalog(query,false,true);
    else if(page==='edit')await editMaker(id);
    else if(page==='maker')await builder(id);
    else if(page==='characters')await characters(query);
    else if(page==='character'){if(!user)return denied();const c=(await api('/characters/'+id)).data;if(seq===routeId)await builder(c.maker_id,c);}
    else if(page==='posts'||page==='admin-posts')await posts(query,page==='admin-posts');
    else if(page==='post')await post(id);
    else if(page==='new-post'||page==='edit-post')await newPost(page==='edit-post'?id:null);
    else if(page==='review')await review(id);
    else if(page==='users')await users(query);
    else app.innerHTML='<h1>Страница не найдена</h1><a href="#catalog">В каталог</a>';
  }catch(e){if(seq===routeId)app.innerHTML='<section class="panel"><h1>Не удалось открыть страницу</h1><p>'+esc(e.message)+'</p><a class="button" href="#catalog">В каталог</a></section>';}
}
window.addEventListener('hashchange',render);
window.addEventListener('icc:unauthorized',()=>{user=null;postDraft=null;header();note('Сессия завершена. Войдите снова.');});
document.addEventListener('click',event=>{
  app.querySelectorAll('.action-menu[open]').forEach(menu=>{if(!menu.contains(event.target))menu.open=false;});
});
document.addEventListener('keydown',event=>{
  if(event.key!=='Escape')return;
  const menu=app.querySelector('.action-menu[open]');
  if(menu){menu.open=false;menu.querySelector('summary').focus();}
});
if(session.token){try{user=(await api('/user')).data;}catch{session.token=null;}}
await render();
