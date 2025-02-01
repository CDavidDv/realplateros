import{S as k,_ as R}from"./AppLayout-DjiVEAkr.js";import{Q as F,d as _,p as f,o as i,e as d,a as e,F as w,h as T,t as u,f as M,x as B,i as O,j as L,l as N,v as j,z as I,B as A,k as U,u as H,C as W,g as E,b as z,c as G,w as J}from"./app-CqWSp48F.js";import{_ as q}from"./_plugin-vue_export-helper-DlAUqK2U.js";const Q={class:"bg-white rounded-lg shadow-md overflow-hidden"},Z={class:"overflow-x-auto"},K={class:"min-w-full divide-y divide-gray-200"},X={class:"bg-white divide-y divide-gray-200"},Y={class:"px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"},ee={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},te={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},se={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},oe={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},ae={key:0},ne={class:"bg-gray-50"},re={class:"px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"},le={__name:"PastesHorneados",setup(S){const{props:l}=F(),x=_(l.pastesHorneados);console.log("horneados: ",x.value);const h=f(()=>{const p={};l.pastesHorneados.forEach(n=>{p[n.relleno]||(p[n.relleno]={totalPiezas:0,items:[]}),p[n.relleno].totalPiezas+=Number(n.piezas),p[n.relleno].items.push(n)});const c=[];return Object.keys(p).forEach(n=>{p[n].items.forEach((g,P)=>{c.push({...g,totalPiezas:p[n].totalPiezas,isFirst:P===0})})}),c}),m=f(()=>h.value.reduce((p,c)=>Number(p)+(c.isFirst?Number(c.totalPiezas):0),0)),y=p=>new Date(p).toLocaleDateString("es-ES",{day:"2-digit",month:"2-digit",year:"numeric",timeZone:"America/Mexico_City"});return(p,c)=>(i(),d("div",null,[c[3]||(c[3]=e("h1",{class:"pb-4 pt-10 font-semibold text-xl"},"Pastes/Empanadas horneados de hoy",-1)),e("div",Q,[e("div",Z,[e("table",K,[c[2]||(c[2]=e("thead",{class:"bg-gray-50"},[e("tr",null,[e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Paste/Empanada"),e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Hora"),e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Día"),e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Piezas Horneadas"),e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Total del día")])],-1)),e("tbody",X,[(i(!0),d(w,null,T(h.value,(n,g)=>(i(),d("tr",{key:g},[e("td",Y,u(n.relleno),1),e("td",ee,u(n.created_at.split("T")[1].split(".")[0]),1),e("td",te,u(y(n.created_at)),1),e("td",se,u(n.piezas),1),e("td",oe,[n.isFirst?(i(),d("span",ae,u(n.totalPiezas),1)):M("",!0)])]))),128))]),e("tfoot",ne,[e("tr",null,[c[0]||(c[0]=e("td",{colspan:"3",class:"px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"},"Total de piezas horneadas:",-1)),e("td",re,u(m.value),1),c[1]||(c[1]=e("td",null,null,-1))])])])])])]))}},ie=q(le,[["__scopeId","data-v-b7d30aea"]]),de={class:"pt-16"},ue={class:"max-w-7xl mx-auto space-y-6"},ce={class:"bg-white rounded-lg shadow p-6"},pe={class:"text-2xl font-bold text-gray-900"},me={class:"mt-4"},ve=["value"],fe={key:0,class:"bg-white rounded-lg shadow overflow-x-auto"},ge={class:"min-w-full divide-y divide-gray-200"},be={class:"bg-gray-50"},xe={class:"bg-white divide-y divide-gray-200"},he={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},ye=["onUpdate:modelValue"],_e={__name:"EstimacionPastes",setup(S){const{props:l}=F(),x=["Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado"],h=new Date().getDay(),m=_(x[h]),y=_(!1),p=[{value:"Lunes",label:"Lunes"},{value:"Martes",label:"Martes"},{value:"Miercoles",label:"Miércoles"},{value:"Jueves",label:"Jueves"},{value:"Viernes",label:"Viernes"},{value:"Sabado",label:"Sábado"},{value:"Domingo",label:"Domingo"}],c=["9:00 am","11:00 am","1:00 pm","3:00 pm","6:00 pm","9:00 pm"],n=f(()=>l.inventario.filter(a=>["pastes","empanadas dulces","empanadas saladas"].includes(a.tipo)).map(a=>a.nombre)),g=_({}),P=k.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:1500,timerProgressBar:!0,didOpen:a=>{a.onmouseenter=k.stopTimer,a.onmouseleave=k.resumeTimer}}),C=()=>{const a={};return c.forEach(s=>{a[s]={},n.value.forEach(t=>{a[s][t]=0})}),a},D=()=>{var s;y.value=!1,g.value=C();const a=(s=l==null?void 0:l.estimaciones)==null?void 0:s.filter(t=>t.dia===m.value);a==null||a.forEach(({hora:t,cantidad:o,inventario_id:r})=>{var $;const v=($=l.inventario.find(V=>V.id===r))==null?void 0:$.nombre;v&&g.value[t]&&(g.value[t][v]=o)}),y.value=!0},b=()=>{I.post("/estimaciones",{estimaciones:g.value,dia:m.value},{preserveState:!1,preserveScroll:!0,onSuccess:()=>P.fire({icon:"success",title:"Sobrantes guardados exitosamente"}),onError:()=>P.fire({icon:"error",title:"Error al guardar los sobrantes"})})};return B(()=>{D()}),O(m,D),(a,s)=>(i(),d("div",de,[e("div",ue,[e("header",ce,[e("h1",pe,"Sistema de Control de Producción - Día: "+u(m.value),1),e("div",me,[L(e("select",{"onUpdate:modelValue":s[0]||(s[0]=t=>m.value=t),class:"rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"},[(i(),d(w,null,T(p,t=>e("option",{key:t.value,value:t.value},u(t.label),9,ve)),64))],512),[[N,m.value]])])]),y.value?(i(),d("div",fe,[e("table",ge,[e("thead",be,[e("tr",null,[s[1]||(s[1]=e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"},"Tiempo",-1)),(i(!0),d(w,null,T(n.value,t=>(i(),d("th",{key:t,class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"},u(t),1))),128))])]),e("tbody",xe,[(i(),d(w,null,T(c,t=>e("tr",{key:t},[e("td",he,u(t),1),(i(!0),d(w,null,T(n.value,o=>(i(),d("td",{key:o,class:"px-6 py-4 whitespace-nowrap"},[L(e("input",{type:"number","onUpdate:modelValue":r=>g.value[t][o]=r,class:"w-20 rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500",min:"0"},null,8,ye),[[j,g.value[t][o]]])]))),128))])),64))])])])):M("",!0),e("div",{class:"w-full flex justify-end"},[e("button",{onClick:b,class:"px-3 py-2 bg-orange-500 rounded-lg text-white hover:bg-orange-400"}," Guardar ")])])]))}},we=A("timerStore",()=>{const S=_(!1),l=_([]),x=_(0),h=_(0),m=_([]),y=_(null),p=f(()=>x.value-h.value),c=b=>{m.value.push(b)},n=b=>9e5,g=()=>{if(m.value.length===0||S.value)return;S.value=!0,l.value=[...m.value],m.value=[],x.value=n(l.value[0]),y.value=Date.now();const b=setInterval(()=>{h.value=Date.now()-y.value,h.value>=x.value&&P(b)},100)},P=b=>{clearInterval(b);const a=[...l.value];S.value=!1,l.value=[],h.value=0,C(),I.post("/hornear",{pastes:a},{preserveScroll:!0,preserveState:!1,replace:!0,onSuccess:()=>{k.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:1500,timerProgressBar:!0,didOpen:t=>{t.onmouseenter=k.stopTimer,t.onmouseleave=k.resumeTimer}}).fire({icon:"success",title:"Registrado correctamente"})},onError:s=>{console.error("Error al registrar el horneado:",s),k.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:1500,timerProgressBar:!0,didOpen:o=>{o.onmouseenter=k.stopTimer,o.onmouseleave=k.resumeTimer}}).fire({icon:"error",title:"Error al registrar"})}})},C=()=>{new Audio("/sound/videoplayback.mp3").play().catch(a=>console.log("Error al reproducir el sonido:",a))};return{horneando:S,pastesHorneando:l,tiempoTotal:x,tiempoTranscurrido:h,tiempoRestante:p,pastesPorHornear:m,agregarPaste:c,iniciarHorneado:g,cancelarPaste:b=>{const a=m.value.findIndex(s=>s.id===b);a!==-1&&m.value.splice(a,1)}}}),$e={class:"grid grid-cols-1 md:grid-cols-2 gap-4"},ke={class:"bg-white shadow rounded-lg p-6"},Te={class:"mb-6"},He={class:"list-disc pl-5"},Pe={class:"capitalize"},Ce={class:"font-semibold"},Se={class:"list-disc pl-5"},De={class:"font-semibold"},Ee={class:"bg-white shadow rounded-lg p-6"},ze=["value"],Me=["max"],Le=["disabled"],Fe={class:"mt-8 bg-white shadow rounded-lg p-6"},Ve={key:0},Be={class:"w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mb-4"},Ne={key:1},je=["disabled"],Ie={class:"mt-8 bg-white shadow rounded-lg p-6"},Re={key:0,class:"text-gray-500 text-center py-4"},Oe={key:1,class:"divide-y divide-gray-200"},Ae=["onClick"],Ue={__name:"SistemaHorneado",setup(S){const l=we(),{props:x}=F(),h=x.inventario,m=f(()=>h.filter(a=>a.tipo==="masa")),y=f(()=>h.filter(a=>a.tipo==="relleno")),p=f(()=>m.value.map(a=>{const s=l.pastesHorneando.filter(t=>t.masa===a.nombre).reduce((t,o)=>t+o.cantidad,0);return{...a,cantidad:a.cantidad-s}})),c=f(()=>y.value.map(a=>{const s=l.pastesHorneando.filter(t=>t.nombre===a.nombre).reduce((t,o)=>t+o.cantidad,0);return{...a,cantidad:a.cantidad-s}})),n=_({masa:"",relleno:"",cantidad:1}),g=a=>{const s={pastes:"bola","empanadas saladas":"salada","empanadas dulces":"dulce"},t=x.inventario.find(r=>(r.nombre.toLowerCase()===a.value.relleno.toLowerCase()||r.nombre.toLowerCase().startsWith(a.value.relleno.toLowerCase()))&&s[r.tipo.toLowerCase()]);return t?s[t.tipo]:"Tipo no válido"},P=()=>{const a=g(n).toLowerCase(),s=p.value.find(o=>o.nombre.toLowerCase()===a),t=c.value.find(o=>o.nombre.toLowerCase()===n.value.relleno.toLowerCase());if(console.log(p.value),console.log("Masa:",s),console.log("Tipo de masa:",a),console.log("Relleno:",t),!s||s.cantidad<=0){k.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:1500,timerProgressBar:!0,didOpen:r=>{r.onmouseenter=k.stopTimer,r.onmouseleave=k.resumeTimer}}).fire({icon:"error",title:"No hay masa suficiente"});return}if(s&&t&&n.value.cantidad<=C.value){s.cantidad-=n.value.cantidad,t.cantidad-=n.value.cantidad;const o=l.pastesPorHornear.find(r=>r.nombre===t.nombre);o?o.cantidad+=n.value.cantidad:l.agregarPaste({id:Date.now(),masa:s.nombre,nombre:t.nombre,cantidad:n.value.cantidad}),n.value={masa:"",relleno:"",cantidad:1}}},C=f(()=>{const a=c.value.find(s=>s.nombre===n.value.relleno);return a?a.cantidad:1}),D=f(()=>n.value.relleno&&n.value.cantidad>0&&n.value.cantidad<=C.value),b=a=>{const s=Math.ceil(a/1e3),t=Math.floor(s/60),o=s%60;return`${t}:${o.toString().padStart(2,"0")}`};return(a,s)=>(i(),d(w,null,[e("div",$e,[e("div",ke,[s[5]||(s[5]=e("h2",{class:"text-xl font-semibold mb-4"},"Ingredientes Disponibles",-1)),e("div",Te,[s[3]||(s[3]=e("h3",{class:"text-lg font-medium mb-2"},"Masas",-1)),e("ul",He,[(i(!0),d(w,null,T(p.value,t=>(i(),d("li",{key:t.id,class:"flex justify-between"},[e("span",Pe,u(t.nombre),1),e("span",Ce,u(t.cantidad)+" unidades",1)]))),128))])]),e("div",null,[s[4]||(s[4]=e("h3",{class:"text-lg font-medium mb-2"},"Rellenos",-1)),e("ul",Se,[(i(!0),d(w,null,T(c.value,t=>(i(),d("li",{key:t.id,class:"flex justify-between"},[e("span",null,u(t.nombre),1),e("span",De,u(t.cantidad)+" unidades",1)]))),128))])])]),e("div",Ee,[s[9]||(s[9]=e("h2",{class:"text-xl font-semibold mb-4"},"Crear Nuevo Paste/Empanada",-1)),e("form",{onSubmit:U(P,["prevent"]),class:"space-y-4"},[e("div",null,[s[7]||(s[7]=e("label",{for:"relleno",class:"block text-sm font-medium text-gray-700"},"Seleccionar Relleno",-1)),L(e("select",{"onUpdate:modelValue":s[0]||(s[0]=t=>n.value.relleno=t),id:"relleno",required:"",class:"mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm rounded-md"},[s[6]||(s[6]=e("option",{value:""},"Seleccione un relleno",-1)),(i(!0),d(w,null,T(y.value,t=>(i(),d("option",{key:t.id,value:t.nombre},u(t.nombre),9,ze))),128))],512),[[N,n.value.relleno]])]),e("div",null,[s[8]||(s[8]=e("label",{for:"cantidad",class:"block text-sm font-medium text-gray-700"},"Cantidad",-1)),L(e("input",{"onUpdate:modelValue":s[1]||(s[1]=t=>n.value.cantidad=t),type:"number",id:"cantidad",required:"",min:"1",max:C.value,class:"mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm"},null,8,Me),[[j,n.value.cantidad,void 0,{number:!0}]])]),e("button",{type:"submit",disabled:!D.value,class:"w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"}," Asignar Unidades ",8,Le)],32)])]),e("div",Fe,[s[12]||(s[12]=e("h2",{class:"text-xl font-semibold mb-4"},"Horno",-1)),H(l).horneando?(i(),d("div",Ve,[s[10]||(s[10]=e("p",{class:"text-lg mb-2"}," Horneando grupo de pastes: ",-1)),e("ul",null,[(i(!0),d(w,null,T(H(l).pastesHorneando,t=>(i(),d("li",{key:t.nombre},u(t.cantidad)+" "+u(t.nombre)+" - masa "+u(t.masa),1))),128))]),e("div",Be,[e("div",{class:"bg-orange-600 h-2.5 rounded-full transition-all duration-100",style:W({width:`${H(l).tiempoTranscurrido/H(l).tiempoTotal*100}%`})},null,4)]),e("p",null,"Tiempo restante: "+u(b(H(l).tiempoRestante)),1)])):(i(),d("div",Ne,[s[11]||(s[11]=e("p",{class:"text-lg mb-4"},"El horno está disponible",-1)),e("button",{onClick:s[2]||(s[2]=(...t)=>H(l).iniciarHorneado&&H(l).iniciarHorneado(...t)),disabled:!H(l).pastesPorHornear.length,class:"w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"}," Iniciar Horneado de Grupo ",8,je)]))]),e("div",Ie,[s[13]||(s[13]=e("h2",{class:"text-xl font-semibold mb-4"},"Grupos de Pastes por Hornear",-1)),H(l).pastesPorHornear.length===0?(i(),d("div",Re," No hay grupos de pastes en la cola de horneado ")):(i(),d("ul",Oe,[(i(!0),d(w,null,T(H(l).pastesPorHornear,t=>(i(),d("li",{key:t.id,class:"py-4 flex justify-between items-center"},[e("span",null,u(t.cantidad)+" "+u(t.nombre)+" - masa "+u(t.masa),1),e("button",{onClick:o=>H(l).cancelarPaste(t.id),class:"bg-red-600 text-white hover:bg-red-800 rounded-lg px-2 py-1 focus:outline-none focus:underline"}," Cancelar ",8,Ae)]))),128))]))])],64))}},We={class:"container mx-auto p-4"},Ge={class:"flex justify-between text-sm"},Je={class:"flex gap-3"},qe={class:"text-lg font-bold text-center text-yellow-800"},Qe={class:"animate-pulse"},Ze={key:0,class:"mt-2"},Ke={class:"font-bold"},Xe={class:"text-lg font-bold text-center text-blue-800"},Ye={class:"animate-pulse"},et={key:0,class:"mt-2"},tt={class:"font-bold"},st={class:"mt-6"},ot={__name:"Hornear",setup(S){const l=_(!1),x=_(!1);function h(){l.value=!l.value}function m(){x.value=!x.value}const y=F(),p=f(()=>y.props.inventario||[]),c=f(()=>y.props.estimaciones||[]),n=_(g());console.log("Estimaciones:",c.value);function g(){const o=new Date;return{day:["Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado"][o.getDay()].toLowerCase(),hour:o.getHours(),minutes:o.getMinutes()}}B(()=>{setInterval(()=>{n.value=g()},6e4)});function P(o){const[r,v]=o.toLowerCase().split(" ");let[$,V]=r.split(":").map(Number);return v==="pm"&&$!==12?$+=12:v==="am"&&$===12&&($=0),{hours:$,minutes:V}}const C=f(()=>p.value.filter(o=>{var r;return["pastes","empanadas dulces","empanadas saladas"].includes((r=o.tipo)==null?void 0:r.toLowerCase())})),D=f(()=>!c.value.length||!C.value.length?[]:c.value.map(o=>{const r=C.value.find($=>$.id===o.inventario_id);if(!r)return null;const{hours:v}=P(o.hora);return{id:`${r.id}-${o.hora}`,nombre:r.nombre,estimado:o.cantidad,existente:r.cantidad,diferencia:r.cantidad-o.cantidad,hora:o.hora,dia:o.dia.toLowerCase(),horaEnNumero:v}}).filter(Boolean).filter((o,r,v)=>r===v.findIndex($=>$.id===o.id))),b=f(()=>D.value.filter(o=>o.dia===n.value.day));f(()=>b.value.filter(o=>o.diferencia<0)),f(()=>b.value.filter(o=>o.diferencia>0));const a=f(()=>b.value.filter(o=>{const r=n.value.hour;return Math.abs(o.horaEnNumero-r)<=1})),s=f(()=>a.value.filter(o=>o.diferencia<0)),t=f(()=>a.value.filter(o=>o.diferencia>0));return(o,r)=>(i(),d("div",We,[e("div",Ge,[r[4]||(r[4]=e("h1",{class:"text-2xl font-bold mb-4"},"Sistema de Horneado",-1)),e("div",Je,[e("div",{class:"p-4 border rounded shadow bg-yellow-100 mb-4 size-fit cursor-pointer",onClick:h},[e("h2",qe,[r[0]||(r[0]=E(" ⚠️ Faltantes ")),e("span",Qe,"("+u(s.value.length)+")",1),r[1]||(r[1]=E(" ⚠️ "))]),l.value?(i(),d("ul",Ze,[(i(!0),d(w,null,T(s.value,v=>(i(),d("li",{key:v.id,class:"mb-2"},[e("span",null,[e("span",Ke,u(v.nombre),1),E(": Faltan "+u(Math.abs(v.diferencia))+" unidades para las "+u(v.hora),1)])]))),128))])):M("",!0)]),e("div",{class:"p-4 border rounded shadow bg-blue-100 mb-4 size-fit cursor-pointer",onClick:m},[e("h2",Xe,[r[2]||(r[2]=E(" ℹ️ Excedentes ")),e("span",Ye,"("+u(t.value.length)+")",1),r[3]||(r[3]=E(" ℹ️ "))]),x.value?(i(),d("ul",et,[(i(!0),d(w,null,T(t.value,v=>(i(),d("li",{key:v.id,class:"mb-2"},[e("span",null,[e("span",tt,u(v.nombre),1),E(": Hay "+u(v.diferencia)+" unidades extra para las "+u(v.hora),1)])]))),128))])):M("",!0)])])]),e("div",st,[z(Ue),z(ie),z(_e)])]))}},at={class:"py-6"},nt={class:"max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 rounded-xl overflow-hidden"},rt={class:"flex justify-between p-6 lg:p-8 bg-white border-b border-gray-200 rounded-2xl"},ut={__name:"index",setup(S){return(l,x)=>(i(),G(R,{title:"Tablero principal"},{default:J(()=>[e("div",at,[e("div",nt,[e("div",rt,[z(ot)])])])]),_:1}))}};export{ut as default};
