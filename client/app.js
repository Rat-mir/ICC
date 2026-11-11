import {api,session,imageBlob} from './api.js';
const $=s=>document.querySelector(s);
const app=$('#app');
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const statuses={draft:'Черновик',pending:'На проверке',approved:'Опубликован',revision:'На доработке',rejected:'Отклонён',hidden:'Скрыт'};
const categories={background:'Фон',body:'Основа',clothes:'Одежда',face:'Лицо',hair:'Причёска',accessory:'Аксессуар'};
let user=null, routeId=0, objectUrls=[];
const isCreator=()=>['creator','admin'].includes(user?.role), isAdmin=()=>user?.role==='admin';
const badge=s=>'<span class="badge '+esc(s)+'">'+esc(statuses[s]||s)+'</span>';
const errors='<div class="form-error" role="alert"></div>';
const field=(name,label,type='text',value='',extra='')=>'<label>'+label+'<input name="'+name+'" type="'+type+'" value="'+esc(value)+'" '+extra+'><span class="field-error" data-field="'+name+'"></span></label>';
const note=s=>{const t=$('#toast');t.textContent=s;t.hidden=false;clearTimeout(note.timer);note.timer=setTimeout(()=>t.hidden=true,4500);};
function header() {
  $('#nav').innerHTML='<a href="#catalog">Каталог</a><a href="#posts">Сообщество</a>'+(user?'<a href="#characters">Мои персонажи</a>':'')+(isCreator()?'<a href="#studio">Мастерская</a>':'')+(isAdmin()?'<a href="#admin">Модерация</a><a href="#users">Аккаунты</a>':'');
  $('#account').innerHTML=user?'<span>'+esc(user.name)+' <span class="badge">'+esc({user:'Пользователь',creator:'Креатор',admin:'Администратор'}[user.role])+'</span></span><button class="secondary small" id="logout">Выйти</button>':'<a href="#login">Войти</a><a class="button small" href="#register">Создать аккаунт</a>';
  $('#logout')?.addEventListener('click',async()=>{try{await api('/logout',{method:'POST'});}catch(e){note(e.message);}session.token=null;user=null;header();location.hash='catalog';await render();});
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
  async like(b){await api('/posts/'+b.dataset.id+'/like',{method:b.dataset.remove?'DELETE':'PUT'});render();},
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
  return '<article class="card"><div class="thumb"><span class="placeholder">✧</span><canvas data-maker-preview="'+m.id+'" width="512" height="512" hidden></canvas></div><div class="card-body"><div class="row"><span class="eyebrow">Maker / '+String(m.id).padStart(2,'0')+'</span>'+badge(m.status)+'</div><h3>'+esc(m.title)+'</h3><p>'+esc(m.description.slice(0,105))+'…</p><div class="row"><span class="muted">'+esc(m.creator?.name||user?.name||'')+'</span><span class="muted">'+(m.assets_count??0)+' слоёв</span></div><div class="actions"><a class="button '+(studio?'secondary':'')+'" href="#'+(studio?'edit':'maker')+'/'+m.id+'">'+(studio?'Редактировать':'Собрать персонажа →')+'</a>'+(studio?'<button class="danger small" data-action="removeMaker" data-id="'+m.id+'">Удалить</button>':'')+'</div></div></article>';
}
async function previews(makers) {
  await Promise.all(makers.map(async m=>{
    try{
      const data=(await api('/makers/'+m.id)).data;
      const canvas=app.querySelector('[data-maker-preview="'+m.id+'"]');if(!canvas)return;
      const ctx=canvas.getContext('2d');
      for(const asset of data.assets||[]) {const url=await imageBlob(asset.image_url);objectUrls.push(url);const img=await new Promise((res,rej)=>{const i=new Image;i.onload=()=>res(i);i.onerror=rej;i.src=url;});ctx.drawImage(img,0,0,512,512);}
      if(canvas.isConnected){canvas.hidden=false;canvas.style='width:100%;height:100%;object-fit:contain';canvas.previousElementSibling.hidden=true;}
    }catch{}
  }));
}
async function catalog(params,studio=false,admin=false) {
  if(studio&&!isCreator())return denied();
  if(admin&&!isAdmin())return denied();
  const p=new URLSearchParams(params);if(studio)p.set('mine','1');
  const result=await api((admin?'/admin':'')+'/makers?'+p);
  const title=studio?'Ваша мастерская':admin?'Проверка мейкеров':'У каждого героя — своя история.';
  app.innerHTML='<section class="hero"><div><span class="eyebrow">Interactive Character Catalog</span><h1>'+title+'</h1><p>'+(studio?'Создавайте наборы PNG-слоёв и отправляйте их на модерацию.':admin?'Проверьте набор, дайте автору обратную связь и примите решение.':'Выберите мир, соберите образ и сохраните его. Всё начинается с одной детали.')+'</p>'+(studio?'<a class="button" href="#edit/new">+ Создать мейкер</a>':'')+'</div><aside class="hero-note"><b>'+(admin?'От заявки к публикации':studio?'Ваша идея. Ваши правила.':'Маленькая студия больших идей')+'</b>Прозрачные PNG, независимые слои и свобода сочетаний.</aside></section><form id="filters" class="toolbar"><label>Поиск<input name="search" value="'+esc(p.get('search')||'')+'" placeholder="Название мейкера"></label><label>Категория<select name="category"><option value="">Все слои</option>'+Object.entries(categories).map(([v,t])=>'<option value="'+v+'" '+(p.get('category')===v?'selected':'')+'>'+t+'</option>').join('')+'</select></label>'+(studio||admin?'<label>Статус<select name="status"><option value="">Все статусы</option>'+Object.entries(statuses).map(([v,t])=>'<option value="'+v+'" '+(p.get('status')===v?'selected':'')+'>'+t+'</option>').join('')+'</select></label>':'')+'<label>Сортировка<select name="sort"><option value="created_at">По дате</option><option value="title" '+(p.get('sort')==='title'?'selected':'')+'>По названию</option></select></label><label>Порядок<select name="order"><option value="desc">По убыванию</option><option value="asc" '+(p.get('order')==='asc'?'selected':'')+'>По возрастанию</option></select></label><button type="submit">Найти</button></form><div class="row"><h2>'+(studio?'Мои наборы':admin?'Заявки и публикации':'Открытая коллекция')+'</h2><span class="muted">'+result.meta.total+' мейкеров</span></div><div class="grid">'+result.data.map(m=>admin?'<article class="panel"><div class="row">'+badge(m.status)+'<span class="muted">#'+m.id+'</span></div><h3>'+esc(m.title)+'</h3><p>'+esc(m.creator?.name)+'</p><a class="button" href="#review/'+m.id+'">Рассмотреть</a></article>':makerCard(m,studio)).join('')+'</div>'+(result.data.length?'':'<div class="empty">Пока ничего не найдено.</div>')+pagination(result.meta,studio?'studio':admin?'admin':'catalog',params);
  form('filters',async(f,d)=>{const q=new URLSearchParams;for(const [k,v] of d)if(v)q.set(k,v);location.hash=(studio?'studio':admin?'admin':'catalog')+'?'+q;});
  bindActions();if(!admin)previews(result.data);
  if(admin){const s=(await api('/admin/statistics')).data;const box=document.createElement('section');box.className='panel';box.innerHTML='<h2>Статистика</h2><div class="stats">'+[['Аккаунтов',s.users],['Персонажей',s.characters],['Публикаций',s.posts],['На проверке',s.makers_by_status.pending||0]].map(([t,n])=>'<div class="stat"><b>'+n+'</b>'+t+'</div>').join('')+'</div>';app.prepend(box);}
}
function denied(){app.innerHTML='<div class="panel"><h1>Нужен доступ</h1><p>Войдите в аккаунт с подходящей ролью.</p><a class="button" href="#login">Войти</a></div>';}
async function authPage(register) {
  app.innerHTML='<section class="panel auth"><span class="eyebrow">Добро пожаловать в ICC</span><h1>'+(register?'Начните свою историю':'С возвращением')+'</h1><p class="muted">'+(register?'Сохраняйте образы и делитесь работами.':'Войдите, чтобы продолжить создание персонажей.')+'</p><form id="auth">'+errors+(register?field('name','Ваше имя','text','','required maxlength="100"'):'')+field('email','Электронная почта','email','','required autocomplete="email"')+field('password','Пароль','password','','required minlength="8" maxlength="128" autocomplete="'+(register?'new-password':'current-password')+'"')+(register?field('password_confirmation','Повторите пароль','password','','required minlength="8" autocomplete="new-password"'):'')+'<button type="submit">'+(register?'Зарегистрироваться':'Войти')+'</button></form><p class="hint">'+(register?'Уже есть аккаунт? <a href="#login">Войти</a>':'Нет аккаунта? <a href="#register">Создать</a>')+'</p></section>';
  form('auth',async(f,d)=>{const r=await api(register?'/register':'/login',{method:'POST',body:Object.fromEntries(d)});session.token=r.token;user=r.user;header();note(register?'Аккаунт создан':'Вы вошли в ICC');location.hash='catalog';});
}
async function editMaker(id) {
  if(!isCreator())return denied();
  const m=id==='new'?null:(await api('/makers/'+id)).data;
  app.innerHTML='<a href="#studio">← В мастерскую</a><h1>'+(m?esc(m.title):'Новый мир начинается здесь')+'</h1>'+(m?badge(m.status):'')+(m?.review_comment?'<div class="review">'+esc(m.review_comment)+'</div>':'')+'<div class="split"><section class="panel"><h2>О мейкере</h2><form id="maker-form">'+errors+field('title','Название','text',m?.title||'','required maxlength="255"')+'<label>Описание<textarea name="description" required maxlength="5000">'+esc(m?.description||'')+'</textarea><span class="field-error" data-field="description"></span></label><button type="submit" style="margin-top:18px">Сохранить</button></form><p class="hint">Изменение опубликованного набора снимает его с публикации до новой проверки.</p>'+(m?'<form id="submit-maker">'+errors+'<span class="field-error" data-field="assets"></span><button type="submit">Отправить на проверку →</button></form>':'')+'</section><section class="panel"><h2>PNG-слои</h2><p class="muted">До 5 МБ · 16–2048 px · одинаковый размер всех PNG. Для проверки обязательны body и face.</p>'+(m?'<form id="asset-form">'+errors+field('name','Название слоя','text','','required')+'<label>Категория<select name="category">'+Object.entries(categories).map(([k,v])=>'<option value="'+k+'">'+v+' ('+k+')</option>').join('')+'</select><span class="field-error" data-field="category"></span></label>'+field('layer_order','Порядок слоя','number','10','required min="0" max="1000"')+field('image','PNG-файл','file','','required accept="image/png"')+'<button type="submit" style="margin-top:15px">Загрузить PNG</button></form>':'<p>Сначала сохраните мейкер.</p>')+'</section></div>'+(m?'<section class="panel"><h2>Элементы набора</h2><div class="asset-list">'+m.assets.map(a=>'<form class="asset-box" data-asset-form="'+a.id+'"><img data-image="'+esc(a.image_url)+'" alt="'+esc(a.name)+'">'+errors+field('name','Название','text',a.name,'required')+'<label>Категория<select name="category">'+Object.entries(categories).map(([k,v])=>'<option value="'+k+'" '+(k===a.category?'selected':'')+'>'+v+'</option>').join('')+'</select></label>'+field('layer_order','Порядок','number',a.layer_order,'min="0" max="1000"')+'<label><input type="checkbox" name="is_active" '+(a.is_active?'checked':'')+'> Активен</label><div class="actions"><button class="small" type="submit">Обновить</button><button type="button" class="danger small" data-delete-asset="'+a.id+'">Удалить</button></div></form>').join('')+'</div></section>':'');
  form('maker-form',async(f,d)=>{const result=await api('/makers'+(m?'/'+id:''),{method:m?'PATCH':'POST',body:Object.fromEntries(d)});note('Мейкер сохранён');if(m)render();else location.hash='edit/'+result.data.id;});
  form('asset-form',async(f,d)=>{await api('/makers/'+id+'/assets',{method:'POST',body:d});note('PNG загружен');render();});
  form('submit-maker',async()=>{await api('/makers/'+id+'/submit',{method:'POST'});note('Заявка отправлена администратору');render();});
  app.querySelectorAll('[data-asset-form]').forEach(f=>{f.id='asset-'+f.dataset.assetForm;form(f.id,async(form,d)=>{const b=Object.fromEntries(d);b.is_active=form.elements.is_active.checked;await api('/makers/'+id+'/assets/'+f.dataset.assetForm,{method:'PATCH',body:b});note('Слой обновлён');render();});});
  app.querySelectorAll('[data-delete-asset]').forEach(b=>b.onclick=async()=>{if(!confirm('Удалить PNG-элемент?'))return;try{await api('/makers/'+id+'/assets/'+b.dataset.deleteAsset,{method:'DELETE'});render();}catch(e){note(e.message);}});
  loadImages();
}
async function builder(id,character=null) {
  const m=(await api('/makers/'+id)).data;
  if(m.status!=='approved'){app.innerHTML='<h1>Мейкер пока недоступен</h1><p>Статус: '+esc(statuses[m.status])+'</p>';return;}
  const groups=Object.keys(categories).filter(c=>m.assets.some(a=>a.category===c));
  let selected=character?character.assets.map(a=>a.id):groups.map(c=>m.assets.find(a=>a.category===c).id);
  const assets=new Map(m.assets.map(a=>[a.id,a]));const imageCache=new Map();
  app.innerHTML='<a href="#catalog">← Каталог</a><div class="row"><div><span class="eyebrow">Студия персонажей</span><h1>'+esc(m.title)+'</h1></div>'+badge(m.status)+'</div><div class="split"><section class="canvas-wrap"><canvas id="character-canvas" width="512" height="512" aria-label="Предпросмотр персонажа"></canvas><div class="actions"><button id="download">Скачать PNG ↓</button></div></section><section class="panel"><p>'+esc(m.description)+'</p><form id="character-form">'+errors+field('name','Имя персонажа','text',character?.name||'Мой герой','required maxlength="255"')+'<h3>Детали образа</h3>'+groups.map(c=>'<label class="layer-row"><span>'+categories[c]+'<select data-category="'+c+'"><option value="">Без слоя</option>'+m.assets.filter(a=>a.category===c&&a.is_active).map(a=>'<option value="'+a.id+'" '+(selected.includes(a.id)?'selected':'')+'>'+esc(a.name)+'</option>').join('')+'</select></span></label>').join('')+'<span class="field-error" data-field="asset_ids"></span><h3>Порядок слоёв</h3><p class="hint">Верхний пункт рисуется первым. Стрелки меняют порядок.</p><div id="layer-order"></div><span class="field-error" data-field="maker_id"></span>'+(user?'<button type="submit" style="margin-top:20px">'+(character?'Сохранить изменения':'Сохранить персонажа')+'</button>':'<p class="hint">Скачивание доступно без регистрации. <a href="#login">Войдите</a>, чтобы сохранять проекты.</p>')+'</form>'+(user?'<form id="publish-canvas" style="margin-top:20px">'+errors+field('title','Название публикации','text',character?.name||'Мой новый герой','required maxlength="255"')+'<button class="secondary" type="submit" style="margin-top:15px">Опубликовать образ</button></form>':'')+'</section></div>';
  const canvas=$('#character-canvas');let paintVersion=0;
  async function paint(){const version=++paintVersion,ctx=canvas.getContext('2d');ctx.clearRect(0,0,canvas.width,canvas.height);for(const id of [...selected]){if(!assets.has(id))continue;if(!imageCache.has(id)){const url=await imageBlob(assets.get(id).image_url);objectUrls.push(url);const img=await new Promise((res,rej)=>{const i=new Image;i.onload=()=>res(i);i.onerror=rej;i.src=url;});imageCache.set(id,img);}if(version!==paintVersion)return;const img=imageCache.get(id);if(ctx.canvas.width!==img.width){ctx.canvas.width=img.width;ctx.canvas.height=img.height;}ctx.drawImage(img,0,0);} }
  function order(){const box=$('#layer-order');box.innerHTML=selected.map((id,i)=>'<div class="layer-order"><span>'+esc(assets.get(id)?.name||'Недоступный слой')+'</span><span><button type="button" class="secondary small" data-move="'+i+'" data-delta="-1" aria-label="Выше">↑</button> <button type="button" class="secondary small" data-move="'+i+'" data-delta="1" aria-label="Ниже">↓</button></span></div>').join('');box.querySelectorAll('[data-move]').forEach(b=>b.onclick=()=>{const i=+b.dataset.move,j=i+(+b.dataset.delta);if(j<0||j>=selected.length)return;[selected[i],selected[j]]=[selected[j],selected[i]];order();paint().catch(e=>note(e.message));});}
  app.querySelectorAll('[data-category]').forEach(s=>s.onchange=()=>{selected=selected.filter(id=>assets.get(id)?.category!==s.dataset.category);if(s.value)selected.push(+s.value);order();paint().catch(e=>note(e.message));});
  order();await paint();
  $('#download').onclick=async()=>{await paint();const a=document.createElement('a');a.download='icc-character.png';a.href=canvas.toDataURL('image/png');a.click();};
  form('character-form',async(f,d)=>{const r=await api('/characters'+(character?'/'+character.id:''),{method:character?'PUT':'POST',body:{name:d.get('name'),maker_id:m.id,asset_ids:selected}});note('Персонаж сохранён');location.hash='character/'+r.data.id;});
  form('publish-canvas',async(f,d)=>{await paint();const blob=await new Promise(res=>canvas.toBlob(res,'image/png'));d.set('image',blob,'character.png');if(character)d.set('character_id',character.id);const r=await api('/posts',{method:'POST',body:d});note('Работа опубликована');location.hash='post/'+r.data.id;});
}
async function characters(params) {
  if(!user)return denied();const r=await api('/characters?'+params);
  app.innerHTML='<span class="eyebrow">Личная коллекция</span><h1>Ваши персонажи</h1><p class="muted">Сохранённые образы всегда под рукой.</p><div class="grid">'+r.data.map(c=>'<article class="panel"><h3>'+esc(c.name)+'</h3><p class="muted">'+esc(c.maker?.title)+'</p><div class="actions"><a class="button" href="#character/'+c.id+'">Открыть</a><button class="danger" data-action="removeCharacter" data-id="'+c.id+'">Удалить</button></div></article>').join('')+'</div>'+(r.data.length?'':'<div class="empty">Создайте первого героя в <a href="#catalog">каталоге мейкеров</a>.</div>')+pagination(r.meta,'characters',params);bindActions();
}
async function posts(params,moderation=false) {
  if(moderation&&!isAdmin())return denied();
  const r=await api((moderation?'/admin':'')+'/posts?'+params);
  app.innerHTML='<div class="row"><div><span class="eyebrow">Сообщество ICC</span><h1>'+(moderation?'Модерация публикаций':'Галерея историй')+'</h1></div>'+(user?'<a class="button" href="#new-post">+ Поделиться работой</a>':'')+(isAdmin()?'<a href="#admin-posts">Модерация постов</a>':'')+'</div><div class="grid">'+r.data.map(p=>'<article class="card"><a class="thumb" href="#post/'+p.id+'"><img data-image="'+esc(p.image_url)+'" alt="'+esc(p.title)+'"></a><div class="card-body"><span class="eyebrow">'+esc(p.author?.name)+'</span><h3><a href="#post/'+p.id+'">'+esc(p.title)+'</a></h3><div class="muted">♡ '+p.likes_count+' · комментариев '+p.comments_count+(p.is_visible?'':' · Скрыт')+'</div>'+(moderation?'<button class="danger small" data-action="hidePost" data-id="'+p.id+'">Скрыть</button>':'')+'</div></article>').join('')+'</div>'+pagination(r.meta,moderation?'admin-posts':'posts',params);bindActions();loadImages();
}
async function post(id) {
  const p=(await api('/posts/'+id)).data;
  app.innerHTML='<a href="#posts">← Сообщество</a><h1>'+esc(p.title)+'</h1><div class="split"><img class="post-image" data-image="'+esc(p.image_url)+'" alt="'+esc(p.title)+'"><section class="panel"><span class="eyebrow">Автор: '+esc(p.author?.name)+'</span><h2>Обсуждение</h2><div class="muted">♡ '+p.likes_count+' · '+p.comments_count+' комментариев</div>'+(user?'<div class="actions"><button data-action="like" data-id="'+id+'">♡ Нравится</button><button class="secondary" data-action="like" data-id="'+id+'" data-remove="1">Снять лайк</button></div>':'')+(user&&(user.id===p.user_id||isAdmin())?'<button class="danger small" data-action="removePost" data-id="'+id+'">Удалить публикацию</button>':'')+(isAdmin()?'<button class="danger small" data-action="hidePost" data-id="'+id+'">Скрыть</button>':'')+'<div>'+p.comments.map(c=>'<article class="comment"><div class="row"><b>'+esc(c.author?.name)+'</b>'+(user&&(user.id===c.user_id||isAdmin())?'<button class="danger small" data-action="removeComment" data-id="'+c.id+'">Удалить</button>':'')+'</div><p>'+esc(c.body)+'</p></article>').join('')+'</div>'+(user?'<form id="comment-form">'+errors+'<label>Ваш комментарий<textarea name="body" required maxlength="2000"></textarea><span class="field-error" data-field="body"></span></label><button type="submit" style="margin-top:12px">Отправить</button></form>':'<p><a href="#login">Войдите</a>, чтобы написать комментарий.</p>')+'</section></div>';bindActions();loadImages();form('comment-form',async(f,d)=>{await api('/posts/'+id+'/comments',{method:'POST',body:Object.fromEntries(d)});render();});
}
async function newPost() {
  if(!user)return denied();const chars=await api('/characters?per_page=100');
  app.innerHTML='<section class="panel auth"><h1>Новая работа</h1><form id="post-form">'+errors+field('title','Название','text','','required maxlength="255"')+'<label>Связать с персонажем<select name="character_id"><option value="">Своё изображение</option>'+chars.data.filter(c=>c.user_id===user.id).map(c=>'<option value="'+c.id+'">'+esc(c.name)+'</option>').join('')+'</select><span class="field-error" data-field="character_id"></span></label>'+field('image','Готовое изображение PNG','file','','required accept="image/png"')+'<p class="hint">PNG до 5 МБ, от 16×16 до 2048×2048 пикселей.</p><button type="submit" style="margin-top:15px">Опубликовать</button></form></section>';
  form('post-form',async(f,d)=>{if(!d.get('character_id'))d.delete('character_id');const r=await api('/posts',{method:'POST',body:d});location.hash='post/'+r.data.id;});
}
async function review(id) {
  if(!isAdmin())return denied();const m=(await api('/makers/'+id)).data;
  app.innerHTML='<a href="#admin">← Все заявки</a><div class="row"><h1>'+esc(m.title)+'</h1>'+badge(m.status)+'</div><p>'+esc(m.description)+'</p><p class="muted">Автор: '+esc(m.creator?.name)+'</p><div class="split"><section class="panel"><h2>Содержимое набора</h2><div class="asset-list">'+m.assets.map(a=>'<div class="asset-box"><img data-image="'+esc(a.image_url)+'" alt="'+esc(a.name)+'"><b>'+esc(a.name)+'</b><p class="muted">'+esc(a.category)+' · слой '+a.layer_order+'</p></div>').join('')+'</div></section><section class="panel"><h2>Решение администратора</h2>'+(m.review_comment?'<div class="review">'+esc(m.review_comment)+'</div>':'')+'<form id="review-form">'+errors+'<label>Решение<select name="decision"><option value="approve">Одобрить</option><option value="request-revision">Вернуть на доработку</option><option value="reject">Отклонить</option></select><span class="field-error" data-field="decision"></span></label><label>Комментарий<textarea name="review_comment" maxlength="2000"></textarea><span class="field-error" data-field="review_comment"></span></label><p class="hint">Причина обязательна при отказе или возврате.</p><button type="submit" style="margin-top:15px">Сохранить решение</button></form>'+(m.status==='approved'?'<div class="actions"><button class="danger" data-action="hideMaker" data-id="'+id+'">Скрыть мейкер</button></div>':'')+'</section></div>';
  form('review-form',async(f,d)=>{await api('/admin/makers/'+id+'/review',{method:'POST',body:Object.fromEntries(d)});note('Решение сохранено');render();});bindActions();loadImages();
}
async function users(params) {
  if(!isAdmin())return denied();const r=await api('/admin/users?'+params);
  app.innerHTML='<span class="eyebrow">Администрирование</span><h1>Аккаунты и роли</h1><p class="muted">Роль креатора выдаёт администратор. Смена роли и блокировка отзывают действующие токены.</p><section class="panel">'+r.data.map(u=>'<form class="user-row" id="user-'+u.id+'">'+errors+'<span><b>'+esc(u.name)+'</b><br><small>'+esc(u.email)+'</small></span><select name="role">'+Object.entries({user:'Пользователь',creator:'Креатор',admin:'Администратор'}).map(([v,t])=>'<option value="'+v+'" '+(v===u.role?'selected':'')+'>'+t+'</option>').join('')+'</select><div><label><input type="checkbox" name="is_blocked" '+(u.is_blocked?'checked':'')+'> Заблокирован</label><button type="submit" class="small" '+(u.id===user.id?'disabled':'')+'>Сохранить</button></div></form>').join('')+'</section>'+pagination(r.meta,'users',params);
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
    else if(page==='character'){if(!user)return denied();const c=(await api('/characters/'+id)).data;await builder(c.maker_id,c);}
    else if(page==='posts'||page==='admin-posts')await posts(query,page==='admin-posts');
    else if(page==='post')await post(id);
    else if(page==='new-post')await newPost();
    else if(page==='review')await review(id);
    else if(page==='users')await users(query);
    else app.innerHTML='<h1>Страница не найдена</h1><a href="#catalog">В каталог</a>';
  }catch(e){if(seq===routeId)app.innerHTML='<section class="panel"><h1>Не удалось открыть страницу</h1><p>'+esc(e.message)+'</p><a class="button" href="#catalog">В каталог</a></section>';}
}
window.addEventListener('hashchange',render);
window.addEventListener('icc:unauthorized',()=>{user=null;header();note('Сессия завершена. Войдите снова.');});
if(session.token){try{user=(await api('/user')).data;}catch{session.token=null;}}
await render();
