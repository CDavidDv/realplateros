import{_ as k}from"./AppLayout-Dtd3Rm2M.js";import{_ as C}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{i as w,d as $,o as l,e as u,a as t,F as b,h as f,t as p,g as v,b as x,u as g,c as M,w as j}from"./app-CIHGODiB.js";/**
 * @license lucide-vue-next v0.473.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const B=s=>s.replace(/([a-z0-9])([A-Z])/g,"$1-$2").toLowerCase();/**
 * @license lucide-vue-next v0.473.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */var h={xmlns:"http://www.w3.org/2000/svg",width:24,height:24,viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":2,"stroke-linecap":"round","stroke-linejoin":"round"};/**
 * @license lucide-vue-next v0.473.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const A=({size:s,strokeWidth:o=2,absoluteStrokeWidth:a,color:r,iconNode:e,name:i,class:c,...d},{slots:n})=>w("svg",{...h,width:s||h.width,height:s||h.height,stroke:r||h.stroke,"stroke-width":a?Number(o)*24/Number(s):o,class:["lucide",`lucide-${B(i??"icon")}`],...d},[...e.map(_=>w(..._)),...n.default?[n.default()]:[]]);/**
 * @license lucide-vue-next v0.473.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const y=(s,o)=>(a,{slots:r})=>w(A,{...a,iconNode:o,name:s},r);/**
 * @license lucide-vue-next v0.473.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const I=y("MinusIcon",[["path",{d:"M5 12h14",key:"1ays0h"}]]);/**
 * @license lucide-vue-next v0.473.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const m=y("PlusIcon",[["path",{d:"M5 12h14",key:"1ays0h"}],["path",{d:"M12 5v14",key:"s699le"}]]),E={class:"min-h-screen bg-gray-50 py-8 px-4"},F={class:"max-w-4xl mx-auto"},L={class:"bg-white shadow-lg rounded-lg overflow-hidden"},T={class:"p-4 bg-gray-100 flex justify-between items-center"},V={class:"text-xl font-semibold text-gray-800"},P={class:"divide-y"},D={class:"text-gray-700 font-medium"},K={class:"flex items-center gap-3"},O={class:"px-3 py-1 bg-gray-200 rounded-full text-gray-800 font-medium"},Q=["onClick"],R=["onClick"],S={class:"flex w-full p-4 text-left text-blue-500 hover:bg-gray-50 transition items-center gap-2"},Z={__name:"Almacen",setup(s){const o=$({Rellenos:{"Mole rojo":10,Frijol:30},Bebidas:{"Cocacola (lata)":10,Fresca:15},Masas:{"M. dulce":10,Bola:20,"M. salada":30},Extras:{"Caja chica":100},Objetos:{Escoba:15,Trapeador:12}}),a=(r,e,i)=>{o.value[r][e]=i};return(r,e)=>(l(),u("div",E,[t("div",F,[e[2]||(e[2]=t("div",{class:"flex justify-between items-center mb-6"},[t("h1",{class:"text-3xl font-bold text-gray-900"},"Almacén"),t("button",{class:"bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded shadow transition"}," Agregar categoría ")],-1)),t("div",L,[(l(!0),u(b,null,f(o.value,(i,c)=>(l(),u("div",{key:c,class:"border-b last:border-b-0"},[t("div",T,[t("h2",V,p(c),1),e[0]||(e[0]=t("button",{class:"text-red-600 hover:text-red-700 font-medium flex items-center gap-1 transition"},[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-5 w-5",viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":"2","stroke-linecap":"round","stroke-linejoin":"round"},[t("line",{x1:"18",y1:"6",x2:"6",y2:"18"}),t("line",{x1:"6",y1:"6",x2:"18",y2:"18"})]),v(" Eliminar categoría ")],-1))]),t("div",P,[(l(!0),u(b,null,f(i,(d,n)=>(l(),u("div",{key:n,class:"flex items-center justify-between p-4 hover:bg-gray-50 transition"},[t("span",D,p(n),1),t("div",K,[t("span",O,p(d),1),t("button",{onClick:_=>a(c,n,d+1),class:"p-1 text-gray-600 hover:text-gray-800 rounded transition"},[x(g(m),{class:"h-5 w-5"})],8,Q),t("button",{onClick:_=>a(c,n,Math.max(0,d-1)),class:"p-1 text-gray-600 hover:text-gray-800 rounded transition"},[x(g(I),{class:"h-5 w-5"})],8,R)])]))),128)),t("button",S,[x(g(m),{class:"h-5 w-5"}),e[1]||(e[1]=v(" Agregar producto "))])])]))),128))])])]))}},q=C(Z,[["__scopeId","data-v-872e969c"]]),G={class:"py-6 px-2"},H={class:"max-w-7xl mx-auto sm:px-6 lg:px-8 py-10"},J={class:"bg-white overflow-hidden shadow-xl rounded-lg"},U={class:"overflow-hidden"},N={__name:"index",setup(s){return(o,a)=>(l(),M(k,{title:"Tablero principal"},{default:j(()=>[t("div",G,[t("div",H,[t("div",J,[t("div",U,[x(q)])])])])]),_:1}))}};export{N as default};
