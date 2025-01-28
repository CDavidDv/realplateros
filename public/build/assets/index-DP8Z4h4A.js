import{i as C,d as v,Q as T,j as L,o as s,e as n,a as e,F as B,h as P,t as w,b as $,u as j,g as M,f,k as _,v as k,c as S,w as U}from"./app-J46ol7dz.js";import{S as V,_ as D}from"./AppLayout-CKUtZhg2.js";import{_ as F}from"./_plugin-vue_export-helper-DlAUqK2U.js";/**
 * @license lucide-vue-next v0.473.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const K=r=>r.replace(/([a-z0-9])([A-Z])/g,"$1-$2").toLowerCase();/**
 * @license lucide-vue-next v0.473.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */var x={xmlns:"http://www.w3.org/2000/svg",width:24,height:24,viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":2,"stroke-linecap":"round","stroke-linejoin":"round"};/**
 * @license lucide-vue-next v0.473.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Q=({size:r,strokeWidth:d=2,absoluteStrokeWidth:o,color:m,iconNode:i,name:u,class:a,...c},{slots:g})=>C("svg",{...x,width:r||x.width,height:r||x.height,stroke:m||x.stroke,"stroke-width":o?Number(d)*24/Number(r):d,class:["lucide",`lucide-${K(u??"icon")}`],...c},[...i.map(p=>C(...p)),...g.default?[g.default()]:[]]);/**
 * @license lucide-vue-next v0.473.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const q=(r,d)=>(o,{slots:m})=>C(Q,{...o,iconNode:d,name:r},m);/**
 * @license lucide-vue-next v0.473.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Z=q("MinusIcon",[["path",{d:"M5 12h14",key:"1ays0h"}]]);/**
 * @license lucide-vue-next v0.473.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const z=q("PlusIcon",[["path",{d:"M5 12h14",key:"1ays0h"}],["path",{d:"M12 5v14",key:"s699le"}]]),G={class:"min-h-screen bg-gray-100 py-8 px-4"},H={class:"max-w-4xl mx-auto space-y-6"},J={class:"flex justify-between items-center mb-6"},O={class:"bg-white shadow-lg rounded-lg overflow-hidden divide-y capitalize"},R={class:"p-4 bg-gray-200 flex justify-between items-center"},X={class:"text-xl font-semibold text-gray-800"},Y=["onClick"],W={class:"divide-y"},ee={class:"text-gray-700 font-medium"},te={class:"flex items-center gap-3"},oe={class:"px-3 py-1 bg-gray-200 rounded-full text-gray-800 font-medium"},se=["onClick"],ae={class:"flex justify-end mt-4"},le={key:0,class:"fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center"},ne={class:"bg-white rounded-lg shadow-lg p-6 max-w-sm w-full"},re={key:0,class:"text-xl font-bold mb-4"},de={key:1,class:"text-xl font-bold mb-4"},ie={key:2},ue={key:3},ce={__name:"Almacen",setup(r){const d=v(T().props.categorias);console.log(d.value);const o=v({}),m=()=>{d.value.forEach(b=>{o.value[b.tipo]=b.productos||{}})};m();const i=v(null),u=v(""),a=v({name:"",quantity:0});let c=null;const g=(b,t=null)=>{i.value=b,c=t},p=()=>{i.value=null,u.value="",a.value={name:"",quantity:0}},I=()=>{u.value.trim()!==""&&(o.value[u.value]||(o.value[u.value]={},p()))},E=()=>{a.value.name.trim()!==""&&a.value.quantity>0&&c&&(o.value[c]||(o.value[c]={}),o.value[c][a.value.name]=a.value.quantity,p())},N=b=>{V.fire({title:"¿Estás seguro?",text:"¡No podrás revertir esto!",icon:"warning",showCancelButton:!0,confirmButtonColor:"#3085d6",cancelButtonColor:"#d33",confirmButtonText:"Sí, eliminar!"}).then(t=>{t.isConfirmed&&(delete o.value[b],V.fire("Eliminado!","La categoría ha sido eliminada.","success"))})};return L(d,m,{deep:!0}),(b,t)=>(s(),n("div",G,[e("div",H,[t[8]||(t[8]=e("div",{class:"flex justify-between items-center mb-6"},[e("h1",{class:"text-4xl font-bold text-gray-900"},"Almacén")],-1)),e("div",J,[e("button",{onClick:t[0]||(t[0]=l=>g("addProduct")),class:"bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition"}," Ingresar productos "),e("button",{onClick:t[1]||(t[1]=l=>g("assignProduct")),class:"bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition"}," Asignar productos ")]),e("div",O,[(s(!0),n(B,null,P(o.value,(l,h)=>(s(),n("div",{key:h,class:"border-b last:border-b-0"},[e("div",R,[e("h2",X,w(h),1),e("button",{onClick:y=>N(h),class:"text-red-600 hover:text-red-700 font-medium flex items-center gap-2 transition"},[$(j(Z),{class:"h-5 w-5"}),t[6]||(t[6]=M(" Eliminar categoría "))],8,Y)]),e("div",W,[(s(!0),n(B,null,P(l,(y,A)=>(s(),n("div",{key:A,class:"flex items-center justify-between p-4 hover:bg-gray-50 transition"},[e("span",ee,w(A),1),e("div",te,[e("span",oe,w(y),1)])]))),128)),e("button",{onClick:y=>g("addProduct",h),class:"flex w-full p-4 text-left text-blue-600 hover:bg-gray-50 transition items-center gap-2"},[$(j(z),{class:"h-5 w-5"}),t[7]||(t[7]=M(" Agregar producto "))],8,se)])]))),128))]),e("div",ae,[e("button",{onClick:t[2]||(t[2]=l=>g("addCategory")),class:"bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition"}," Agregar categoría ")])]),i.value?(s(),n("div",le,[e("div",ne,[i.value==="addCategory"?(s(),n("h2",re,"Agregar categoría")):f("",!0),i.value==="addProduct"?(s(),n("h2",de,"Agregar producto")):f("",!0),i.value==="addCategory"?(s(),n("div",ie,[_(e("input",{"onUpdate:modelValue":t[3]||(t[3]=l=>u.value=l),type:"text",placeholder:"Nombre de la categoría",class:"w-full border border-gray-300 rounded-lg px-4 py-2 mb-4 focus:ring-blue-500 focus:border-blue-500"},null,512),[[k,u.value]]),e("button",{onClick:I,class:"bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg w-full transition"}," Agregar ")])):f("",!0),i.value==="addProduct"?(s(),n("div",ue,[_(e("input",{"onUpdate:modelValue":t[4]||(t[4]=l=>a.value.name=l),type:"text",placeholder:"Nombre del producto",class:"w-full border border-gray-300 rounded-lg px-4 py-2 mb-4 focus:ring-blue-500 focus:border-blue-500"},null,512),[[k,a.value.name]]),_(e("input",{"onUpdate:modelValue":t[5]||(t[5]=l=>a.value.quantity=l),type:"number",placeholder:"Cantidad",class:"w-full border border-gray-300 rounded-lg px-4 py-2 mb-4 focus:ring-blue-500 focus:border-blue-500"},null,512),[[k,a.value.quantity,void 0,{number:!0}]]),e("button",{onClick:E,class:"bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg w-full transition"}," Agregar ")])):f("",!0),e("button",{onClick:p,class:"text-gray-500 hover:text-gray-600 mt-4 block mx-auto"}," Cancelar ")])])):f("",!0)]))}},ge=F(ce,[["__scopeId","data-v-80da0bef"]]),be={class:"py-6 px-2"},me={class:"max-w-7xl mx-auto sm:px-6 lg:px-8 py-10"},pe={class:"bg-white overflow-hidden shadow-xl rounded-lg"},ve={class:"overflow-hidden"},ye={__name:"index",setup(r){return(d,o)=>(s(),S(D,{title:"Tablero principal"},{default:U(()=>[e("div",be,[e("div",me,[e("div",pe,[e("div",ve,[$(ge)])])])])]),_:1}))}};export{ye as default};
