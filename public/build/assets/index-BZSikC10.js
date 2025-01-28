import{S as $,_ as I}from"./AppLayout-CKUtZhg2.js";import{Q as F,d as _,p as g,o as r,e as l,a as e,F as w,h as k,t as u,f as M,x as V,j as O,k as L,z as B,v as N,y as j,B as A,l as U,u as T,C as W,g as E,b as z,c as G,w as J}from"./app-J46ol7dz.js";import{_ as q}from"./_plugin-vue_export-helper-DlAUqK2U.js";const Q={class:"bg-white rounded-lg shadow-md overflow-hidden"},K={class:"overflow-x-auto"},X={class:"min-w-full divide-y divide-gray-200"},Y={class:"bg-white divide-y divide-gray-200"},Z={class:"px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"},ee={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},te={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},se={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},ae={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},oe={key:0},ne={class:"bg-gray-50"},re={class:"px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"},le={__name:"PastesHorneados",setup(P){const{props:i}=F();_(i.pastesHorneados);const x=g(()=>{const m={};i.pastesHorneados.forEach(c=>{m[c.relleno]||(m[c.relleno]={totalPiezas:0,items:[]}),m[c.relleno].totalPiezas+=Number(c.piezas),m[c.relleno].items.push(c)});const p=[];return Object.keys(m).forEach(c=>{m[c].items.forEach((d,h)=>{p.push({...d,totalPiezas:m[c].totalPiezas,isFirst:h===0})})}),p}),y=g(()=>x.value.reduce((m,p)=>Number(m)+(p.isFirst?Number(p.totalPiezas):0),0)),v=m=>new Date(m).toLocaleDateString("es-ES",{day:"2-digit",month:"2-digit",year:"numeric"});return(m,p)=>(r(),l("div",null,[p[3]||(p[3]=e("h1",{class:"pb-4 pt-10 font-semibold text-xl"},"Pastes/Empanadas horneados de hoy",-1)),e("div",Q,[e("div",K,[e("table",X,[p[2]||(p[2]=e("thead",{class:"bg-gray-50"},[e("tr",null,[e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Paste/Empanada"),e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Hora"),e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Día"),e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Piezas Horneadas"),e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Total del día")])],-1)),e("tbody",Y,[(r(!0),l(w,null,k(x.value,(c,d)=>(r(),l("tr",{key:d},[e("td",Z,u(c.relleno),1),e("td",ee,u(c.hora.split(" ")[1]),1),e("td",te,u(v(c.fecha)),1),e("td",se,u(c.piezas),1),e("td",ae,[c.isFirst?(r(),l("span",oe,u(c.totalPiezas),1)):M("",!0)])]))),128))]),e("tfoot",ne,[e("tr",null,[p[0]||(p[0]=e("td",{colspan:"3",class:"px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"},"Total de piezas horneadas:",-1)),e("td",re,u(y.value),1),p[1]||(p[1]=e("td",null,null,-1))])])])])])]))}},ie=q(le,[["__scopeId","data-v-0a159d47"]]),de={class:"pt-16"},ue={class:"max-w-7xl mx-auto space-y-6"},ce={class:"bg-white rounded-lg shadow p-6"},me={class:"text-2xl font-bold text-gray-900"},pe={class:"mt-4"},ve=["value"],fe={key:0,class:"bg-white rounded-lg shadow overflow-x-auto"},ge={class:"min-w-full divide-y divide-gray-200"},be={class:"bg-gray-50"},xe={class:"bg-white divide-y divide-gray-200"},he={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},ye=["onUpdate:modelValue"],_e={__name:"EstimacionPastes",setup(P){const{props:i}=F(),x=["Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado"],y=new Date().getDay(),v=_(x[y]),m=_(!1),p=[{value:"Lunes",label:"Lunes"},{value:"Martes",label:"Martes"},{value:"Miercoles",label:"Miércoles"},{value:"Jueves",label:"Jueves"},{value:"Viernes",label:"Viernes"},{value:"Sabado",label:"Sábado"},{value:"Domingo",label:"Domingo"}],c=["9:00 am","11:00 am","1:00 pm","3:00 pm","6:00 pm","9:00 pm"],d=g(()=>i.inventario.filter(o=>["pastes","empanadas dulces","empanadas saladas"].includes(o.tipo)).map(o=>o.nombre)),h=_({}),S=$.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:1500,timerProgressBar:!0,didOpen:o=>{o.onmouseenter=$.stopTimer,o.onmouseleave=$.resumeTimer}}),H=()=>{const o={};return c.forEach(s=>{o[s]={},d.value.forEach(t=>{o[s][t]=0})}),o},D=()=>{m.value=!1,h.value=H(),i.estimaciones.filter(s=>s.dia===v.value).forEach(({hora:s,cantidad:t,inventario_id:a})=>{var b;const n=(b=i.inventario.find(C=>C.id===a))==null?void 0:b.nombre;n&&h.value[s]&&(h.value[s][n]=t)}),m.value=!0},f=()=>{j.post("/estimaciones",{estimaciones:h.value,dia:v.value},{preserveState:!1,preserveScroll:!0,onSuccess:()=>S.fire({icon:"success",title:"Sobrantes guardados exitosamente"}),onError:()=>S.fire({icon:"error",title:"Error al guardar los sobrantes"})})};return V(()=>{D()}),O(v,D),(o,s)=>(r(),l("div",de,[e("div",ue,[e("header",ce,[e("h1",me,"Sistema de Control de Producción - Día: "+u(v.value),1),e("div",pe,[L(e("select",{"onUpdate:modelValue":s[0]||(s[0]=t=>v.value=t),class:"rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"},[(r(),l(w,null,k(p,t=>e("option",{key:t.value,value:t.value},u(t.label),9,ve)),64))],512),[[B,v.value]])])]),m.value?(r(),l("div",fe,[e("table",ge,[e("thead",be,[e("tr",null,[s[1]||(s[1]=e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"},"Tiempo",-1)),(r(!0),l(w,null,k(d.value,t=>(r(),l("th",{key:t,class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"},u(t),1))),128))])]),e("tbody",xe,[(r(),l(w,null,k(c,t=>e("tr",{key:t},[e("td",he,u(t),1),(r(!0),l(w,null,k(d.value,a=>(r(),l("td",{key:a,class:"px-6 py-4 whitespace-nowrap"},[L(e("input",{type:"number","onUpdate:modelValue":n=>h.value[t][a]=n,class:"w-20 rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500",min:"0"},null,8,ye),[[N,h.value[t][a]]])]))),128))])),64))])])])):M("",!0),e("div",{class:"w-full flex justify-end"},[e("button",{onClick:f,class:"px-3 py-2 bg-orange-500 rounded-lg text-white hover:bg-orange-400"}," Guardar ")])])]))}},we=A("timerStore",()=>{const P=_(!1),i=_([]),x=_(0),y=_(0),v=_([]),m=_(null),p=g(()=>x.value-y.value),c=f=>{v.value.push(f)},d=f=>(f.masa==="bola"||f.masa==="salada"||f.masa==="dulce",9e5),h=()=>{if(v.value.length===0||P.value)return;P.value=!0,i.value=[...v.value],v.value=[],x.value=d(i.value[0]),m.value=Date.now();const f=setInterval(()=>{y.value=Date.now()-m.value,y.value>=x.value&&S(f)},100)},S=f=>{clearInterval(f);const o=[...i.value];P.value=!1,i.value=[],y.value=0,H(),j.post("/hornear",{pastes:o},{preserveScroll:!0,preserveState:!1,replace:!0,onSuccess:()=>{$.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:1500,timerProgressBar:!0,didOpen:t=>{t.onmouseenter=$.stopTimer,t.onmouseleave=$.resumeTimer}}).fire({icon:"success",title:"Registrado correctamente"})},onError:s=>{console.error("Error al registrar el horneado:",s),$.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:1500,timerProgressBar:!0,didOpen:a=>{a.onmouseenter=$.stopTimer,a.onmouseleave=$.resumeTimer}}).fire({icon:"error",title:"Error al registrar"})}})},H=()=>{new Audio("/sound/videoplayback.mp3").play().catch(o=>console.log("Error al reproducir el sonido:",o))};return{horneando:P,pastesHorneando:i,tiempoTotal:x,tiempoTranscurrido:y,tiempoRestante:p,pastesPorHornear:v,agregarPaste:c,iniciarHorneado:h,cancelarPaste:f=>{const o=v.value.findIndex(s=>s.id===f);o!==-1&&v.value.splice(o,1)}}}),$e={class:"grid grid-cols-1 md:grid-cols-2 gap-4"},ke={class:"bg-white shadow rounded-lg p-6"},Te={class:"mb-6"},He={class:"list-disc pl-5"},Pe={class:"capitalize"},Ce={class:"font-semibold"},Se={class:"list-disc pl-5"},De={class:"font-semibold"},Ee={class:"bg-white shadow rounded-lg p-6"},ze=["value"],Me=["max"],Le=["disabled"],Fe={class:"mt-8 bg-white shadow rounded-lg p-6"},Ve={key:0},Be={class:"w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mb-4"},Ne={key:1},je=["disabled"],Re={class:"mt-8 bg-white shadow rounded-lg p-6"},Ie={key:0,class:"text-gray-500 text-center py-4"},Oe={key:1,class:"divide-y divide-gray-200"},Ae=["onClick"],Ue={__name:"SistemaHorneado",setup(P){const i=we(),{props:x}=F(),y=x.inventario,v=g(()=>y.filter(o=>o.tipo==="masa")),m=g(()=>y.filter(o=>o.tipo==="relleno")),p=g(()=>v.value.map(o=>{const s=i.pastesHorneando.filter(t=>t.masa===o.nombre).reduce((t,a)=>t+a.cantidad,0);return{...o,cantidad:o.cantidad-s}})),c=g(()=>m.value.map(o=>{const s=i.pastesHorneando.filter(t=>t.nombre===o.nombre).reduce((t,a)=>t+a.cantidad,0);return{...o,cantidad:o.cantidad-s}})),d=_({masa:"",relleno:"",cantidad:1}),h=o=>{const s={pastes:"bola","empanadas saladas":"salada","empanadas dulces":"dulce"},t=x.inventario.find(n=>(n.nombre.toLowerCase()===o.value.relleno.toLowerCase()||n.nombre.toLowerCase().startsWith(o.value.relleno.toLowerCase()))&&s[n.tipo.toLowerCase()]);return t?s[t.tipo]:"Tipo no válido"},S=()=>{const o=h(d).toLowerCase(),s=p.value.find(a=>a.nombre.toLowerCase()===o),t=c.value.find(a=>a.nombre.toLowerCase()===d.value.relleno.toLowerCase());if(console.log(p.value),console.log("Masa:",s),console.log("Tipo de masa:",o),console.log("Relleno:",t),!s||s.cantidad<=0){$.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:1500,timerProgressBar:!0,didOpen:n=>{n.onmouseenter=$.stopTimer,n.onmouseleave=$.resumeTimer}}).fire({icon:"error",title:"No hay masa suficiente"});return}if(s&&t&&d.value.cantidad<=H.value){s.cantidad-=d.value.cantidad,t.cantidad-=d.value.cantidad;const a=i.pastesPorHornear.find(n=>n.nombre===t.nombre);a?a.cantidad+=d.value.cantidad:i.agregarPaste({id:Date.now(),masa:s.nombre,nombre:t.nombre,cantidad:d.value.cantidad}),d.value={masa:"",relleno:"",cantidad:1}}},H=g(()=>{const o=c.value.find(s=>s.nombre===d.value.relleno);return o?o.cantidad:1}),D=g(()=>d.value.relleno&&d.value.cantidad>0&&d.value.cantidad<=H.value),f=o=>{const s=Math.ceil(o/1e3),t=Math.floor(s/60),a=s%60;return`${t}:${a.toString().padStart(2,"0")}`};return(o,s)=>(r(),l(w,null,[e("div",$e,[e("div",ke,[s[5]||(s[5]=e("h2",{class:"text-xl font-semibold mb-4"},"Ingredientes Disponibles",-1)),e("div",Te,[s[3]||(s[3]=e("h3",{class:"text-lg font-medium mb-2"},"Masas",-1)),e("ul",He,[(r(!0),l(w,null,k(p.value,t=>(r(),l("li",{key:t.id,class:"flex justify-between"},[e("span",Pe,u(t.nombre),1),e("span",Ce,u(t.cantidad)+" unidades",1)]))),128))])]),e("div",null,[s[4]||(s[4]=e("h3",{class:"text-lg font-medium mb-2"},"Rellenos",-1)),e("ul",Se,[(r(!0),l(w,null,k(c.value,t=>(r(),l("li",{key:t.id,class:"flex justify-between"},[e("span",null,u(t.nombre),1),e("span",De,u(t.cantidad)+" unidades",1)]))),128))])])]),e("div",Ee,[s[9]||(s[9]=e("h2",{class:"text-xl font-semibold mb-4"},"Crear Nuevo Paste/Empanada",-1)),e("form",{onSubmit:U(S,["prevent"]),class:"space-y-4"},[e("div",null,[s[7]||(s[7]=e("label",{for:"relleno",class:"block text-sm font-medium text-gray-700"},"Seleccionar Relleno",-1)),L(e("select",{"onUpdate:modelValue":s[0]||(s[0]=t=>d.value.relleno=t),id:"relleno",required:"",class:"mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm rounded-md"},[s[6]||(s[6]=e("option",{value:""},"Seleccione un relleno",-1)),(r(!0),l(w,null,k(m.value,t=>(r(),l("option",{key:t.id,value:t.nombre},u(t.nombre),9,ze))),128))],512),[[B,d.value.relleno]])]),e("div",null,[s[8]||(s[8]=e("label",{for:"cantidad",class:"block text-sm font-medium text-gray-700"},"Cantidad",-1)),L(e("input",{"onUpdate:modelValue":s[1]||(s[1]=t=>d.value.cantidad=t),type:"number",id:"cantidad",required:"",min:"1",max:H.value,class:"mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm"},null,8,Me),[[N,d.value.cantidad,void 0,{number:!0}]])]),e("button",{type:"submit",disabled:!D.value,class:"w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"}," Asignar Unidades ",8,Le)],32)])]),e("div",Fe,[s[12]||(s[12]=e("h2",{class:"text-xl font-semibold mb-4"},"Horno",-1)),T(i).horneando?(r(),l("div",Ve,[s[10]||(s[10]=e("p",{class:"text-lg mb-2"}," Horneando grupo de pastes: ",-1)),e("ul",null,[(r(!0),l(w,null,k(T(i).pastesHorneando,t=>(r(),l("li",{key:t.nombre},u(t.cantidad)+" "+u(t.nombre)+" - masa "+u(t.masa),1))),128))]),e("div",Be,[e("div",{class:"bg-orange-600 h-2.5 rounded-full transition-all duration-100",style:W({width:`${T(i).tiempoTranscurrido/T(i).tiempoTotal*100}%`})},null,4)]),e("p",null,"Tiempo restante: "+u(f(T(i).tiempoRestante)),1)])):(r(),l("div",Ne,[s[11]||(s[11]=e("p",{class:"text-lg mb-4"},"El horno está disponible",-1)),e("button",{onClick:s[2]||(s[2]=(...t)=>T(i).iniciarHorneado&&T(i).iniciarHorneado(...t)),disabled:!T(i).pastesPorHornear.length,class:"w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"}," Iniciar Horneado de Grupo ",8,je)]))]),e("div",Re,[s[13]||(s[13]=e("h2",{class:"text-xl font-semibold mb-4"},"Grupos de Pastes por Hornear",-1)),T(i).pastesPorHornear.length===0?(r(),l("div",Ie," No hay grupos de pastes en la cola de horneado ")):(r(),l("ul",Oe,[(r(!0),l(w,null,k(T(i).pastesPorHornear,t=>(r(),l("li",{key:t.id,class:"py-4 flex justify-between items-center"},[e("span",null,u(t.cantidad)+" "+u(t.nombre)+" - masa "+u(t.masa),1),e("button",{onClick:a=>T(i).cancelarPaste(t.id),class:"bg-red-600 text-white hover:bg-red-800 rounded-lg px-2 py-1 focus:outline-none focus:underline"}," Cancelar ",8,Ae)]))),128))]))])],64))}},We={class:"container mx-auto p-4"},Ge={class:"flex justify-between text-sm"},Je={class:"flex gap-3"},qe={class:"text-lg font-bold text-center text-yellow-800"},Qe={class:"animate-pulse"},Ke={key:0,class:"mt-2"},Xe={class:"font-bold"},Ye={class:"text-lg font-bold text-center text-blue-800"},Ze={class:"animate-pulse"},et={key:0,class:"mt-2"},tt={class:"font-bold"},st={class:"mt-6"},at={__name:"Hornear",setup(P){const i=_(!1),x=_(!1);function y(){i.value=!i.value}function v(){x.value=!x.value}const m=F(),p=g(()=>m.props.inventario||[]),c=g(()=>m.props.estimaciones||[]),d=_(h());function h(){const a=new Date;return{day:["Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado"][a.getDay()].toLowerCase(),hour:a.getHours(),minutes:a.getMinutes()}}V(()=>{setInterval(()=>{d.value=h()},6e4)});function S(a){const[n,b]=a.toLowerCase().split(" ");let[C,R]=n.split(":").map(Number);return b==="pm"&&C!==12?C+=12:b==="am"&&C===12&&(C=0),{hours:C,minutes:R}}const H=g(()=>p.value.filter(a=>{var n;return["pastes","empanadas dulces","empanadas saladas"].includes((n=a.tipo)==null?void 0:n.toLowerCase())})),D=g(()=>!c.value.length||!H.value.length?[]:c.value.map(a=>{const n=H.value.find(C=>C.id===a.inventario_id);if(!n)return null;const{hours:b}=S(a.hora);return{id:`${n.id}-${a.hora}`,nombre:n.nombre,estimado:a.cantidad,existente:n.cantidad,diferencia:n.cantidad-a.cantidad,hora:a.hora,dia:a.dia.toLowerCase(),horaEnNumero:b}}).filter(Boolean)),f=g(()=>D.value.filter(a=>a.dia===d.value.day));g(()=>f.value.filter(a=>a.diferencia<0)),g(()=>f.value.filter(a=>a.diferencia>0));const o=g(()=>f.value.filter(a=>{const n=d.value.hour;return Math.abs(a.horaEnNumero-n)<=1})),s=g(()=>o.value.filter(a=>a.diferencia<0)),t=g(()=>o.value.filter(a=>a.diferencia>0));return(a,n)=>(r(),l("div",We,[e("div",Ge,[n[4]||(n[4]=e("h1",{class:"text-2xl font-bold mb-4"},"Sistema de Horneado",-1)),e("div",Je,[e("div",{class:"p-4 border rounded shadow bg-yellow-100 mb-4 size-fit cursor-pointer",onClick:y},[e("h2",qe,[n[0]||(n[0]=E(" ⚠️ Faltantes ")),e("span",Qe,"("+u(s.value.length)+")",1),n[1]||(n[1]=E(" ⚠️ "))]),i.value?(r(),l("ul",Ke,[(r(!0),l(w,null,k(s.value,b=>(r(),l("li",{key:b.id,class:"mb-2"},[e("span",null,[e("span",Xe,u(b.nombre),1),E(": Faltan "+u(Math.abs(b.diferencia))+" unidades para las "+u(b.hora),1)])]))),128))])):M("",!0)]),e("div",{class:"p-4 border rounded shadow bg-blue-100 mb-4 size-fit cursor-pointer",onClick:v},[e("h2",Ye,[n[2]||(n[2]=E(" ℹ️ Excedentes ")),e("span",Ze,"("+u(t.value.length)+")",1),n[3]||(n[3]=E(" ℹ️ "))]),x.value?(r(),l("ul",et,[(r(!0),l(w,null,k(t.value,b=>(r(),l("li",{key:b.id,class:"mb-2"},[e("span",null,[e("span",tt,u(b.nombre),1),E(": Hay "+u(b.diferencia)+" unidades extra para las "+u(b.hora),1)])]))),128))])):M("",!0)])])]),e("div",st,[z(Ue),z(ie),z(_e)])]))}},ot={class:"py-6"},nt={class:"max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 rounded-xl overflow-hidden"},rt={class:"flex justify-between p-6 lg:p-8 bg-white border-b border-gray-200 rounded-2xl"},ut={__name:"index",setup(P){return(i,x)=>(r(),G(I,{title:"Tablero principal"},{default:J(()=>[e("div",ot,[e("div",nt,[e("div",rt,[z(at)])])])]),_:1}))}};export{ut as default};
