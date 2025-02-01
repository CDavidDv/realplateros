import{S as T,_ as R}from"./AppLayout-j0Bh0Z_N.js";import{Q as F,d as w,p as v,o as l,e as d,a as e,F as $,h as C,t as u,f as z,x as B,i as A,j as L,l as N,v as j,z as I,B as O,k as U,u as P,C as W,g as E,b as M,c as G,w as J}from"./app-nrOlodX3.js";import{_ as q}from"./_plugin-vue_export-helper-DlAUqK2U.js";const Z={class:"bg-white rounded-lg shadow-md overflow-hidden"},Q={class:"overflow-x-auto"},X={class:"min-w-full divide-y divide-gray-200"},K={class:"bg-white divide-y divide-gray-200"},Y={class:"px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"},ee={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},te={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},se={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},oe={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},ae={key:0},ne={class:"bg-gray-50"},re={class:"px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"},le={__name:"PastesHorneados",setup(D){const{props:i}=F();w(i.pastesHorneados);const g=v(()=>{const m={};i.pastesHorneados.forEach(a=>{m[a.relleno]||(m[a.relleno]={totalPiezas:0,items:[]}),m[a.relleno].totalPiezas+=Number(a.piezas),m[a.relleno].items.push(a)});const c=[];return Object.keys(m).forEach(a=>{m[a].items.forEach((_,h)=>{c.push({..._,totalPiezas:m[a].totalPiezas,isFirst:h===0})})}),c}),y=v(()=>g.value.reduce((m,c)=>Number(m)+(c.isFirst?Number(c.totalPiezas):0),0)),x=m=>new Date(m).toLocaleString("es-MX",{hour:"2-digit",minute:"2-digit",second:"2-digit",timeZone:"America/Mexico_City"}),b=m=>new Date(m).toLocaleDateString("es-ES",{day:"2-digit",month:"2-digit",year:"numeric",timeZone:"America/Mexico_City"});return(m,c)=>(l(),d("div",null,[c[3]||(c[3]=e("h1",{class:"pb-4 pt-10 font-semibold text-xl"},"Pastes/Empanadas horneados de hoy",-1)),e("div",Z,[e("div",Q,[e("table",X,[c[2]||(c[2]=e("thead",{class:"bg-gray-50"},[e("tr",null,[e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Paste/Empanada"),e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Hora"),e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Día"),e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Piezas Horneadas"),e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Total del día")])],-1)),e("tbody",K,[(l(!0),d($,null,C(g.value,(a,_)=>(l(),d("tr",{key:_},[e("td",Y,u(a.relleno),1),e("td",ee,u(x(a.updated_at)),1),e("td",te,u(b(a.created_at)),1),e("td",se,u(a.piezas),1),e("td",oe,[a.isFirst?(l(),d("span",ae,u(a.totalPiezas),1)):z("",!0)])]))),128))]),e("tfoot",ne,[e("tr",null,[c[0]||(c[0]=e("td",{colspan:"3",class:"px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"},"Total de piezas horneadas:",-1)),e("td",re,u(y.value),1),c[1]||(c[1]=e("td",null,null,-1))])])])])])]))}},ie=q(le,[["__scopeId","data-v-d6bcbfb9"]]),de={class:"pt-16"},ue={class:"max-w-7xl mx-auto space-y-6"},ce={class:"bg-white rounded-lg shadow p-6"},me={class:"text-2xl font-bold text-gray-900"},pe={class:"mt-4"},ve=["value"],fe={key:0,class:"bg-white rounded-lg shadow overflow-x-auto"},ge={class:"min-w-full divide-y divide-gray-200"},be={class:"bg-gray-50"},xe={class:"bg-white divide-y divide-gray-200"},he={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},ye=["onUpdate:modelValue"],_e={__name:"EstimacionPastes",setup(D){const{props:i}=F(),g=["Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado"],y=new Date().getDay(),x=g[y],b=w(x||"Lunes"),m=w(!1),c=[{value:"Lunes",label:"Lunes"},{value:"Martes",label:"Martes"},{value:"Miercoles",label:"Miércoles"},{value:"Jueves",label:"Jueves"},{value:"Viernes",label:"Viernes"},{value:"Sábado",label:"Sábado"},{value:"Domingo",label:"Domingo"}],a=["9:00 am","11:00 am","1:00 pm","3:00 pm","6:00 pm","9:00 pm"],_=v(()=>i.inventario.filter(t=>["pastes","empanadas dulces","empanadas saladas"].includes(t.tipo)).map(t=>t.nombre)),h=w({}),H=T.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:1500,timerProgressBar:!0,didOpen:t=>{t.onmouseenter=T.stopTimer,t.onmouseleave=T.resumeTimer}}),S=()=>{const t={};return a.forEach(s=>{t[s]={},_.value.forEach(o=>{t[s][o]=0})}),t},f=()=>{m.value=!1,h.value=S(),i.estimaciones.filter(s=>s.dia===b.value).forEach(({hora:s,cantidad:o,inventario_id:n})=>{var k;const p=(k=i.inventario.find(V=>V.id===n))==null?void 0:k.nombre;p&&h.value[s]&&(h.value[s][p]=o)}),m.value=!0},r=()=>{I.post("/estimaciones",{estimaciones:h.value,dia:b.value},{preserveState:!1,preserveScroll:!0,onSuccess:()=>H.fire({icon:"success",title:"Sobrantes guardados exitosamente"}),onError:()=>H.fire({icon:"error",title:"Error al guardar los sobrantes"})})};return B(()=>{f()}),A(b,()=>{f(),console.log("Cambio de día detectado:",h.value)}),console.log("Estimaciones recibidas:",i.estimaciones),(t,s)=>(l(),d("div",de,[e("div",ue,[e("header",ce,[e("h1",me,"Sistema de Control de Producción - Día: "+u(b.value),1),e("div",pe,[L(e("select",{"onUpdate:modelValue":s[0]||(s[0]=o=>b.value=o),class:"rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"},[(l(),d($,null,C(c,o=>e("option",{key:o.value,value:o.value},u(o.label),9,ve)),64))],512),[[N,b.value]])])]),m.value?(l(),d("div",fe,[e("table",ge,[e("thead",be,[e("tr",null,[s[1]||(s[1]=e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"},"Tiempo",-1)),(l(!0),d($,null,C(_.value,o=>(l(),d("th",{key:o,class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"},u(o),1))),128))])]),e("tbody",xe,[(l(),d($,null,C(a,o=>e("tr",{key:o},[e("td",he,u(o),1),(l(!0),d($,null,C(_.value,n=>(l(),d("td",{key:n,class:"px-6 py-4 whitespace-nowrap"},[L(e("input",{type:"number","onUpdate:modelValue":p=>h.value[o][n]=p,class:"w-20 rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500",min:"0"},null,8,ye),[[j,h.value[o][n]]])]))),128))])),64))])])])):z("",!0),e("div",{class:"w-full flex justify-end"},[e("button",{onClick:r,class:"px-3 py-2 bg-orange-500 rounded-lg text-white hover:bg-orange-400"}," Guardar ")])])]))}},we=O("timerStore",()=>{const D=w(!1),i=w([]),g=w(0),y=w(0),x=w([]),b=w(null),m=v(()=>g.value-y.value),c=f=>{x.value.push(f)},a=f=>9e5,_=()=>{if(x.value.length===0||D.value)return;D.value=!0,i.value=[...x.value],x.value=[],g.value=a(i.value[0]),b.value=Date.now();const f=setInterval(()=>{y.value=Date.now()-b.value,y.value>=g.value&&h(f)},100)},h=f=>{clearInterval(f);const r=[...i.value];D.value=!1,i.value=[],y.value=0,H(),I.post("/hornear",{pastes:r},{preserveScroll:!0,preserveState:!1,replace:!0,onSuccess:()=>{T.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:1500,timerProgressBar:!0,didOpen:s=>{s.onmouseenter=T.stopTimer,s.onmouseleave=T.resumeTimer}}).fire({icon:"success",title:"Registrado correctamente"})},onError:t=>{console.error("Error al registrar el horneado:",t),T.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:1500,timerProgressBar:!0,didOpen:o=>{o.onmouseenter=T.stopTimer,o.onmouseleave=T.resumeTimer}}).fire({icon:"error",title:"Error al registrar"})}})},H=()=>{new Audio("/sound/videoplayback.mp3").play().catch(r=>console.log("Error al reproducir el sonido:",r))};return{horneando:D,pastesHorneando:i,tiempoTotal:g,tiempoTranscurrido:y,tiempoRestante:m,pastesPorHornear:x,agregarPaste:c,iniciarHorneado:_,cancelarPaste:f=>{const r=x.value.findIndex(t=>t.id===f);r!==-1&&x.value.splice(r,1)}}}),$e={class:"grid grid-cols-1 md:grid-cols-2 gap-4"},ke={class:"bg-white shadow rounded-lg p-6"},Te={class:"mb-6"},Ce={class:"list-disc pl-5"},He={class:"capitalize"},Pe={class:"font-semibold"},De={class:"list-disc pl-5"},Se={class:"font-semibold"},Ee={class:"bg-white shadow rounded-lg p-6"},Me=["value"],ze=["max"],Le=["disabled"],Fe={class:"mt-8 bg-white shadow rounded-lg p-6"},Ve={key:0},Be={class:"w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mb-4"},Ne={key:1},je=["disabled"],Ie={class:"mt-8 bg-white shadow rounded-lg p-6"},Re={key:0,class:"text-gray-500 text-center py-4"},Ae={key:1,class:"divide-y divide-gray-200"},Oe=["onClick"],Ue={__name:"SistemaHorneado",setup(D){const i=we(),{props:g}=F(),y=g.inventario,x=v(()=>y.filter(r=>r.tipo==="masa")),b=v(()=>y.filter(r=>r.tipo==="relleno")),m=v(()=>x.value.map(r=>{const t=i.pastesHorneando.filter(s=>s.masa===r.nombre).reduce((s,o)=>s+o.cantidad,0);return{...r,cantidad:r.cantidad-t}})),c=v(()=>b.value.map(r=>{const t=i.pastesHorneando.filter(s=>s.nombre===r.nombre).reduce((s,o)=>s+o.cantidad,0);return{...r,cantidad:r.cantidad-t}})),a=w({masa:"",relleno:"",cantidad:1}),_=r=>{const t={pastes:"bola","empanadas saladas":"salada","empanadas dulces":"dulce"},s=g.inventario.find(n=>(n.nombre.toLowerCase()===r.value.relleno.toLowerCase()||n.nombre.toLowerCase().startsWith(r.value.relleno.toLowerCase()))&&t[n.tipo.toLowerCase()]);return s?t[s.tipo]:"Tipo no válido"},h=()=>{const r=_(a).toLowerCase(),t=m.value.find(o=>o.nombre.toLowerCase()===r),s=c.value.find(o=>o.nombre.toLowerCase()===a.value.relleno.toLowerCase());if(console.log(m.value),console.log("Masa:",t),console.log("Tipo de masa:",r),console.log("Relleno:",s),!t||t.cantidad<=0){T.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:1500,timerProgressBar:!0,didOpen:n=>{n.onmouseenter=T.stopTimer,n.onmouseleave=T.resumeTimer}}).fire({icon:"error",title:"No hay masa suficiente"});return}if(t&&s&&a.value.cantidad<=H.value){t.cantidad-=a.value.cantidad,s.cantidad-=a.value.cantidad;const o=i.pastesPorHornear.find(n=>n.nombre===s.nombre);o?o.cantidad+=a.value.cantidad:i.agregarPaste({id:Date.now(),masa:t.nombre,nombre:s.nombre,cantidad:a.value.cantidad}),a.value={masa:"",relleno:"",cantidad:1}}},H=v(()=>{const r=c.value.find(t=>t.nombre===a.value.relleno);return r?r.cantidad:1}),S=v(()=>a.value.relleno&&a.value.cantidad>0&&a.value.cantidad<=H.value),f=r=>{const t=Math.ceil(r/1e3),s=Math.floor(t/60),o=t%60;return`${s}:${o.toString().padStart(2,"0")}`};return(r,t)=>(l(),d($,null,[e("div",$e,[e("div",ke,[t[5]||(t[5]=e("h2",{class:"text-xl font-semibold mb-4"},"Ingredientes Disponibles",-1)),e("div",Te,[t[3]||(t[3]=e("h3",{class:"text-lg font-medium mb-2"},"Masas",-1)),e("ul",Ce,[(l(!0),d($,null,C(m.value,s=>(l(),d("li",{key:s.id,class:"flex justify-between"},[e("span",He,u(s.nombre),1),e("span",Pe,u(s.cantidad)+" unidades",1)]))),128))])]),e("div",null,[t[4]||(t[4]=e("h3",{class:"text-lg font-medium mb-2"},"Rellenos",-1)),e("ul",De,[(l(!0),d($,null,C(c.value,s=>(l(),d("li",{key:s.id,class:"flex justify-between"},[e("span",null,u(s.nombre),1),e("span",Se,u(s.cantidad)+" unidades",1)]))),128))])])]),e("div",Ee,[t[9]||(t[9]=e("h2",{class:"text-xl font-semibold mb-4"},"Crear Nuevo Paste/Empanada",-1)),e("form",{onSubmit:U(h,["prevent"]),class:"space-y-4"},[e("div",null,[t[7]||(t[7]=e("label",{for:"relleno",class:"block text-sm font-medium text-gray-700"},"Seleccionar Relleno",-1)),L(e("select",{"onUpdate:modelValue":t[0]||(t[0]=s=>a.value.relleno=s),id:"relleno",required:"",class:"mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm rounded-md"},[t[6]||(t[6]=e("option",{value:""},"Seleccione un relleno",-1)),(l(!0),d($,null,C(b.value,s=>(l(),d("option",{key:s.id,value:s.nombre},u(s.nombre),9,Me))),128))],512),[[N,a.value.relleno]])]),e("div",null,[t[8]||(t[8]=e("label",{for:"cantidad",class:"block text-sm font-medium text-gray-700"},"Cantidad",-1)),L(e("input",{"onUpdate:modelValue":t[1]||(t[1]=s=>a.value.cantidad=s),type:"number",id:"cantidad",required:"",min:"1",max:H.value,class:"mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm"},null,8,ze),[[j,a.value.cantidad,void 0,{number:!0}]])]),e("button",{type:"submit",disabled:!S.value,class:"w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"}," Asignar Unidades ",8,Le)],32)])]),e("div",Fe,[t[12]||(t[12]=e("h2",{class:"text-xl font-semibold mb-4"},"Horno",-1)),P(i).horneando?(l(),d("div",Ve,[t[10]||(t[10]=e("p",{class:"text-lg mb-2"}," Horneando grupo de pastes: ",-1)),e("ul",null,[(l(!0),d($,null,C(P(i).pastesHorneando,s=>(l(),d("li",{key:s.nombre},u(s.cantidad)+" "+u(s.nombre)+" - masa "+u(s.masa),1))),128))]),e("div",Be,[e("div",{class:"bg-orange-600 h-2.5 rounded-full transition-all duration-100",style:W({width:`${P(i).tiempoTranscurrido/P(i).tiempoTotal*100}%`})},null,4)]),e("p",null,"Tiempo restante: "+u(f(P(i).tiempoRestante)),1)])):(l(),d("div",Ne,[t[11]||(t[11]=e("p",{class:"text-lg mb-4"},"El horno está disponible",-1)),e("button",{onClick:t[2]||(t[2]=(...s)=>P(i).iniciarHorneado&&P(i).iniciarHorneado(...s)),disabled:!P(i).pastesPorHornear.length,class:"w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"}," Iniciar Horneado de Grupo ",8,je)]))]),e("div",Ie,[t[13]||(t[13]=e("h2",{class:"text-xl font-semibold mb-4"},"Grupos de Pastes por Hornear",-1)),P(i).pastesPorHornear.length===0?(l(),d("div",Re," No hay grupos de pastes en la cola de horneado ")):(l(),d("ul",Ae,[(l(!0),d($,null,C(P(i).pastesPorHornear,s=>(l(),d("li",{key:s.id,class:"py-4 flex justify-between items-center"},[e("span",null,u(s.cantidad)+" "+u(s.nombre)+" - masa "+u(s.masa),1),e("button",{onClick:o=>P(i).cancelarPaste(s.id),class:"bg-red-600 text-white hover:bg-red-800 rounded-lg px-2 py-1 focus:outline-none focus:underline"}," Cancelar ",8,Oe)]))),128))]))])],64))}},We={class:"container mx-auto p-4"},Ge={class:"flex justify-between text-sm"},Je={class:"flex gap-3"},qe={class:"text-lg font-bold text-center text-yellow-800"},Ze={class:"animate-pulse"},Qe={key:0,class:"mt-2"},Xe={class:"font-bold"},Ke={class:"text-lg font-bold text-center text-blue-800"},Ye={class:"animate-pulse"},et={key:0,class:"mt-2"},tt={class:"font-bold"},st={class:"mt-6"},ot={__name:"Hornear",setup(D){const i=w(!1),g=w(!1);function y(){i.value=!i.value}function x(){g.value=!g.value}const b=F(),m=v(()=>b.props.inventario||[]),c=v(()=>b.props.estimacionesHoy||[]),a=w(_());function _(){const o=new Date;return{day:["Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado"][o.getDay()].toLowerCase(),hour:o.getHours(),minutes:o.getMinutes()}}B(()=>{setInterval(()=>{a.value=_()},6e4)});function h(o){const[n,p]=o.toLowerCase().split(" ");let[k,V]=n.split(":").map(Number);return p==="pm"&&k!==12?k+=12:p==="am"&&k===12&&(k=0),{hours:k,minutes:V}}const H=v(()=>m.value.filter(o=>{var n;return["pastes","empanadas dulces","empanadas saladas"].includes((n=o.tipo)==null?void 0:n.toLowerCase())})),S=v(()=>!c.value.length||!H.value.length?[]:c.value.map(o=>{const n=H.value.find(k=>k.id===o.inventario_id);if(!n)return null;const{hours:p}=h(o.hora);return{id:`${n.id}-${o.hora}`,nombre:n.nombre,estimado:o.cantidad,existente:n.cantidad,diferencia:n.cantidad-o.cantidad,hora:o.hora,dia:o.dia.toLowerCase(),horaEnNumero:p}}).filter(Boolean).filter((o,n,p)=>n===p.findIndex(k=>k.id===o.id))),f=v(()=>S.value.filter(o=>o.dia===a.value.day));v(()=>f.value.filter(o=>o.diferencia<0)),v(()=>f.value.filter(o=>o.diferencia>0));const r=v(()=>f.value.filter(o=>{const n=a.value.hour;return Math.abs(o.horaEnNumero-n)<=1})),t=v(()=>r.value.filter(o=>o.diferencia<0)),s=v(()=>r.value.filter(o=>o.diferencia>0));return(o,n)=>(l(),d("div",We,[e("div",Ge,[n[4]||(n[4]=e("h1",{class:"text-2xl font-bold mb-4"},"Sistema de Horneado",-1)),e("div",Je,[e("div",{class:"p-4 border rounded shadow bg-yellow-100 mb-4 size-fit cursor-pointer",onClick:y},[e("h2",qe,[n[0]||(n[0]=E(" ⚠️ Faltantes ")),e("span",Ze,"("+u(t.value.length)+")",1),n[1]||(n[1]=E(" ⚠️ "))]),i.value?(l(),d("ul",Qe,[(l(!0),d($,null,C(t.value,p=>(l(),d("li",{key:p.id,class:"mb-2"},[e("span",null,[e("span",Xe,u(p.nombre),1),E(": Faltan "+u(Math.abs(p.diferencia))+" unidades para las "+u(p.hora),1)])]))),128))])):z("",!0)]),e("div",{class:"p-4 border rounded shadow bg-blue-100 mb-4 size-fit cursor-pointer",onClick:x},[e("h2",Ke,[n[2]||(n[2]=E(" ℹ️ Excedentes ")),e("span",Ye,"("+u(s.value.length)+")",1),n[3]||(n[3]=E(" ℹ️ "))]),g.value?(l(),d("ul",et,[(l(!0),d($,null,C(s.value,p=>(l(),d("li",{key:p.id,class:"mb-2"},[e("span",null,[e("span",tt,u(p.nombre),1),E(": Hay "+u(p.diferencia)+" unidades extra para las "+u(p.hora),1)])]))),128))])):z("",!0)])])]),e("div",st,[M(Ue),M(ie),M(_e)])]))}},at={class:"py-6"},nt={class:"max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 rounded-xl overflow-hidden"},rt={class:"flex justify-between p-6 lg:p-8 bg-white border-b border-gray-200 rounded-2xl"},ut={__name:"index",setup(D){return(i,g)=>(l(),G(R,{title:"Tablero principal"},{default:J(()=>[e("div",at,[e("div",nt,[e("div",rt,[M(ot)])])])]),_:1}))}};export{ut as default};
