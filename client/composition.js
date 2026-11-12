import {imageBlob} from './api.js';

export function defaultLayers(assets) {
  const layers = new Map();
  for (const asset of assets) {
    if (asset.is_active && !layers.has(asset.category)) layers.set(asset.category, asset);
  }
  const style=assets.find(asset=>asset.vector)?.vector.style;
  if (assets.some(asset=>asset.vector?.dicebear)) {
    return [...layers.values()].filter(asset=>asset.vector?.required || asset.category==='background'
      || (style==='critters' && asset.category==='hair'));
  }
  if (style && style!=='pony') layers.delete('accessory');
  const defaults={
    chibi:{hair:[0,'#8280bf'],clothes:[0,'#8274bd'],face:[0,'#434256'],body:[0,'#f9ddca']},
    anime:{hair:[0,'#d3a66d'],clothes:[0,'#f2e9ed'],body:[0,'#f9ddca']},
    furry:{hair:[0,'#944932'],clothes:[2,'#649898'],face:[1,'#a48b42']},
    pony:{clothes:[2,'#c77e9f'],accessory:[0,'#c2afea'],face:[0,'#5879aa']},
  };
  for (const [category,[variant,color]] of Object.entries(defaults[style]||{})) {
    const option=assets.find(asset=>asset.is_active && asset.category===category && asset.vector.variant===variant && asset.vector.color===color);
    if(option)layers.set(category,option);
  }
  return [...layers.values()];
}

export function createComposer() {
  const cache = new Map();
  let version = 0;

  function load(asset, back = false, parts = null) {
    const key=parts ? 'dicebear:'+parts : asset.id+(back?':back':'');
    if (!cache.has(key)) {
      const loading = (async () => {
        const address = new URL(asset.image_url);
        if(back)address.searchParams.set('plane','back');
        if(parts)address.searchParams.set('parts',parts);
        const url = await imageBlob(address);
        try {
          const image = new Image();
          image.src = url;
          await image.decode();
          return image;
        } finally {
          URL.revokeObjectURL(url);
        }
      })().catch(error => {
        cache.delete(key);
        throw error;
      });
      cache.set(key, loading);
    }
    return cache.get(key);
  }

  async function draw(canvas, layers, latestOnly = true) {
    const request = latestOnly ? ++version : null;
    const behind=layers.filter(asset=>asset.vector?.back);
    const planes=layers.map(asset=>[asset,false]);
    const background=layers.findIndex(asset=>asset.category==='background');
    planes.splice(background+1,0,...behind.map(asset=>[asset,true]));
    const images = layers[0]?.vector?.dicebear
      ? [await load(layers[0], false, layers.map(asset=>asset.id).join(','))]
      : await Promise.all(planes.map(([asset,back])=>load(asset,back)));
    if (latestOnly && request !== version) return;
    canvas.width = images[0]?.naturalWidth || 512;
    canvas.height = images[0]?.naturalHeight || 512;
    const context = canvas.getContext('2d');
    for (const image of images) context.drawImage(image, 0, 0);
  }

  async function png(layers) {
    const canvas = document.createElement('canvas');
    await draw(canvas, layers, false);
    return new Promise((resolve, reject) => {
      canvas.toBlob(blob => blob ? resolve(blob) : reject(new Error('Не удалось создать PNG.')), 'image/png');
    });
  }

  async function thumbnail(asset) {
    const sample=document.createElement('canvas');
    await draw(sample,[asset],false);
    const thumb=document.createElement('canvas');
    thumb.width=thumb.height=84;
    const context=thumb.getContext('2d');
    const bounds=asset.vector?visibleBounds(sample):[0,0,sample.width,sample.height];
    const [x,y,w,h]=bounds;
    const scale=78/Math.max(w,h);
    context.drawImage(sample,x,y,w,h,(84-w*scale)/2,(84-h*scale)/2,w*scale,h*scale);
    return thumb.toDataURL();
  }

  return {draw, png, thumbnail};
}

function visibleBounds(canvas) {
  const {width,height}=canvas;
  const {data}=canvas.getContext('2d').getImageData(0,0,width,height);
  let left=width,top=height,right=-1,bottom=-1;
  for(let y=0;y<height;y++){
    for(let x=0;x<width;x++){
      if(data[(y*width+x)*4+3]===0)continue;
      left=Math.min(left,x);
      top=Math.min(top,y);
      right=Math.max(right,x);
      bottom=y;
    }
  }
  return right<left?[0,0,width,height]:[left,top,right-left+1,bottom-top+1];
}
