const base = window.ICC_CONFIG.apiUrl.replace(/\/$/, '');
export const session = {
  get token() { return sessionStorage.getItem('icc_token'); },
  set token(value) { value ? sessionStorage.setItem('icc_token', value) : sessionStorage.removeItem('icc_token'); }
};
export class ApiError extends Error {
  constructor(status, payload) { super(payload.message || 'Ошибка запроса'); this.status=status; this.errors=payload.errors || {}; }
}
export async function api(path, options={}) {
  const {method='GET', body, binary=false}=options;
  const headers={Accept: binary ? 'image/png' : 'application/json'};
  if (session.token) headers.Authorization='Bearer '+session.token;
  const multipart=body instanceof FormData;
  if(body!==undefined && !multipart) headers['Content-Type']='application/json';
  let response;
  try { response=await fetch(base+path, {method,headers,body:body===undefined?undefined:multipart?body:JSON.stringify(body)}); }
  catch { throw new ApiError(0,{message:'Сервер недоступен. Проверьте запуск Laravel и адрес API в config.js.'}); }
  if(response.status===204) return null;
  if(!response.ok) {
    const payload=await response.json().catch(()=>({message:'Ошибка сервера. Повторите запрос позже.'}));
    if(response.status===401 && session.token) { session.token=null; window.dispatchEvent(new Event('icc:unauthorized')); location.hash='login'; }
    throw new ApiError(response.status,payload);
  }
  return binary ? response.blob() : response.json();
}
export async function imageBlob(url) {
  const path=new URL(url).pathname.replace(/^\/api/,'');
  return URL.createObjectURL(await api(path,{binary:true}));
}
