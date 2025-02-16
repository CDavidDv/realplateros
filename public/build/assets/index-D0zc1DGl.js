import{S as T,_ as oe}from"./AppLayout-BsLvSEoW.js";import{Q as R,d as _,p as b,o as r,e as l,a as e,F as E,h as S,t as u,f as I,x as j,i as ne,j as A,l as X,v as K,z as Y,k as re,B as le,y as Q,g as V,b as N,c as ie,w as de}from"./app-DwoSQ7gG.js";import{_ as ue}from"./_plugin-vue_export-helper-DlAUqK2U.js";const ce={class:"bg-white rounded-lg shadow-md overflow-hidden"},me={class:"overflow-x-auto"},pe={class:"min-w-full divide-y divide-gray-200"},ve={class:"bg-white divide-y divide-gray-200"},fe={class:"px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"},be={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},he={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},ge={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},xe={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},_e={key:0},ye={class:"bg-gray-50"},we={class:"px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"},$e={__name:"PastesHorneados",setup(B){const{props:d}=R();_(d.pastesHorneados);const C=b(()=>{const p={};d.pastesHorneados.forEach(c=>{p[c.relleno]||(p[c.relleno]={totalPiezas:0,items:[]}),p[c.relleno].totalPiezas+=Number(c.piezas),p[c.relleno].items.push(c)});const i=[];return Object.keys(p).forEach(c=>{p[c].items.forEach((y,k)=>{i.push({...y,totalPiezas:p[c].totalPiezas,isFirst:k===0})})}),i}),w=b(()=>C.value.reduce((p,i)=>Number(p)+(i.isFirst?Number(i.totalPiezas):0),0)),$=p=>new Date(p).toLocaleString("es-MX",{hour:"2-digit",minute:"2-digit",second:"2-digit",timeZone:"America/Mexico_City"}),h=p=>new Date(p).toLocaleDateString("es-ES",{day:"2-digit",month:"2-digit",year:"numeric",timeZone:"America/Mexico_City"});return(p,i)=>(r(),l("div",null,[i[3]||(i[3]=e("h1",{class:"pb-4 pt-10 font-semibold text-xl"},"Pastes/Empanadas horneados de hoy",-1)),e("div",ce,[e("div",me,[e("table",pe,[i[2]||(i[2]=e("thead",{class:"bg-gray-50"},[e("tr",null,[e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Paste/Empanada"),e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Hora"),e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Día"),e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Piezas Horneadas"),e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"},"Total del día")])],-1)),e("tbody",ve,[(r(!0),l(E,null,S(C.value,(c,y)=>(r(),l("tr",{key:y},[e("td",fe,u(c.relleno),1),e("td",be,u($(c.updated_at)),1),e("td",he,u(h(c.updated_at)),1),e("td",ge,u(c.piezas),1),e("td",xe,[c.isFirst?(r(),l("span",_e,u(c.totalPiezas),1)):I("",!0)])]))),128))]),e("tfoot",ye,[e("tr",null,[i[0]||(i[0]=e("td",{colspan:"3",class:"px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"},"Total de piezas horneadas:",-1)),e("td",we,u(w.value),1),i[1]||(i[1]=e("td",null,null,-1))])])])])])]))}},ke=ue($e,[["__scopeId","data-v-b730beee"]]),De={class:"pt-16"},Ee={class:"max-w-7xl mx-auto space-y-6"},Ce={class:"bg-white rounded-lg shadow p-6"},Se={class:"text-2xl font-bold text-gray-900"},He={class:"mt-4"},Me=["value"],Te={key:0,class:"bg-white rounded-lg shadow overflow-x-auto"},Pe={class:"min-w-full divide-y divide-gray-200"},Le={class:"bg-gray-50"},ze={class:"bg-white divide-y divide-gray-200"},Fe={class:"px-6 py-4 whitespace-nowrap text-sm text-gray-500"},Ve=["onUpdate:modelValue"],Be={__name:"EstimacionPastes",setup(B){const{props:d}=R(),C=["Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado"],w=new Date().getDay(),$=C[w],h=_($||"Lunes"),p=_(!1),i=[{value:"Lunes",label:"Lunes"},{value:"Martes",label:"Martes"},{value:"Miercoles",label:"Miércoles"},{value:"Jueves",label:"Jueves"},{value:"Viernes",label:"Viernes"},{value:"Sábado",label:"Sábado"},{value:"Domingo",label:"Domingo"}],c=["9:00 am","11:00 am","1:00 pm","3:00 pm","6:00 pm","9:00 pm"],y=b(()=>d.inventario.filter(f=>["pastes","empanadas dulces","empanadas saladas"].includes(f.tipo)).map(f=>f.nombre)),k=_({}),L=T.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:1500,timerProgressBar:!0,didOpen:f=>{f.onmouseenter=T.stopTimer,f.onmouseleave=T.resumeTimer}}),z=()=>{const f={};return c.forEach(g=>{f[g]={},y.value.forEach(v=>{f[g][v]=0})}),f},P=()=>{p.value=!1,k.value=z(),d.estimaciones.filter(g=>g.dia===h.value).forEach(({hora:g,cantidad:v,inventario_id:H})=>{var t;const M=(t=d.inventario.find(n=>n.id===H))==null?void 0:t.nombre;M&&k.value[g]&&(k.value[g][M]=v)}),p.value=!0},F=()=>{Y.post("/estimaciones",{estimaciones:k.value,dia:h.value},{preserveState:!1,preserveScroll:!0,onSuccess:()=>L.fire({icon:"success",title:"Sobrantes guardados exitosamente"}),onError:()=>L.fire({icon:"error",title:"Error al guardar los sobrantes"})})};return j(()=>{P()}),ne(h,()=>{P()}),(f,g)=>(r(),l("div",De,[e("div",Ee,[e("header",Ce,[e("h1",Se,"Sistema de Control de Producción - Día: "+u(h.value),1),e("div",He,[A(e("select",{"onUpdate:modelValue":g[0]||(g[0]=v=>h.value=v),class:"rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"},[(r(),l(E,null,S(i,v=>e("option",{key:v.value,value:v.value},u(v.label),9,Me)),64))],512),[[X,h.value]])])]),p.value?(r(),l("div",Te,[e("table",Pe,[e("thead",Le,[e("tr",null,[g[1]||(g[1]=e("th",{class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"},"Tiempo",-1)),(r(!0),l(E,null,S(y.value,v=>(r(),l("th",{key:v,class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"},u(v),1))),128))])]),e("tbody",ze,[(r(),l(E,null,S(c,v=>e("tr",{key:v},[e("td",Fe,u(v),1),(r(!0),l(E,null,S(y.value,H=>(r(),l("td",{key:H,class:"px-6 py-4 whitespace-nowrap"},[A(e("input",{type:"number","onUpdate:modelValue":M=>k.value[v][H]=M,class:"w-20 rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500",min:"0"},null,8,Ve),[[K,k.value[v][H]]])]))),128))])),64))])])])):I("",!0),e("div",{class:"w-full flex justify-end"},[e("button",{onClick:F,class:"px-3 py-2 bg-orange-500 rounded-lg text-white hover:bg-orange-400"}," Guardar ")])])]))}},Ne={class:"grid grid-cols-1 md:grid-cols-2 gap-4"},Ie={class:"bg-white shadow rounded-lg p-6"},je={class:"mb-6"},Ae={class:"list-disc pl-5"},Re={class:"capitalize"},Oe={class:"font-semibold"},Ue={class:"list-disc pl-5"},We={class:"font-semibold"},Ge={class:"bg-white shadow rounded-lg p-6"},Je=["value"],qe=["max"],Ze=["disabled"],Qe={class:"mt-8 bg-white shadow rounded-lg p-6"},Xe={key:0},Ke={class:"w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mb-4"},Ye={key:1},et=["disabled"],tt={class:"mt-8 bg-white shadow rounded-lg p-6"},at={key:0,class:"text-gray-500 text-center py-4"},st={key:1,class:"divide-y divide-gray-200"},ot=["onClick"],nt={__name:"SistemaHorneado",setup(B){var G,J,q,Z;const{props:d}=R(),C=d.inventario,w=_(((G=d==null?void 0:d.horno)==null?void 0:G.estado)||!1),$=_(w.value&&((J=d==null?void 0:d.horno)!=null&&J.pastesHorneando)?d.horno.pastesHorneando:[]),h=_(15*60*1e3),p=_(0),i=_([]),c=_(w.value?new Date((q=d==null?void 0:d.horno)==null?void 0:q.tiempo_inicio).getTime():0),y=_(w.value?new Date((Z=d==null?void 0:d.horno)==null?void 0:Z.tiempo_fin).getTime():0);let k=null;const L=()=>{if(!(i.value.length===0||w.value)){w.value=!0,$.value=[...i.value],i.value=[],c.value=Date.now(),y.value=c.value+h.value;try{Q.post("/iniciar-horneado",{tiempo_inicio:c.value,tiempo_fin:y.value,pastes_horneando:$.value,estado:!0}).then(o=>{}).catch(o=>{console.error("Error al iniciar el horneado:",o)}),T.fire({icon:"success",title:"Horneando",toast:!0,position:"top-end",showConfirmButton:!1,timer:1500}),W()}catch(o){console.error("Error al iniciar el horneado:",o),T.fire({icon:"error",title:"Error al iniciar",toast:!0,position:"top-end",showConfirmButton:!1,timer:1500})}}},z=T.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:3e3,timerProgressBar:!0,didOpen:o=>{o.addEventListener("mouseenter",T.stopTimer),o.addEventListener("mouseleave",T.resumeTimer)}}),P=()=>{if(F())return;clearInterval(k);const o=[...$.value];w.value=!1,$.value=[],p.value=0,f(),Y.post("/hornear",{pastes:o},{preserveScroll:!0,preserveState:!1,replace:!0,onSuccess:()=>{z.fire({icon:"success",title:"Horneado finalizado"})},onError:a=>{console.error("Error al registrar el horneado:",a),z.fire({icon:"error",title:"Error al registrar el horneado"})}})},F=()=>{Q.post("/check-estado",{pastes:$.value}).then(o=>o.data.estado).catch(o=>(console.error("Error al obtener el estado del horno:",o),!1))},f=()=>{new Audio("/sound/videoplayback.mp3").play().catch(a=>console.error("Error al reproducir el sonido:",a))},g=b(()=>C.filter(o=>o.tipo==="masa")),v=b(()=>C.filter(o=>o.tipo==="relleno")),H=b(()=>g.value.map(o=>{const a=$.value.filter(s=>s.masa===o.nombre).reduce((s,x)=>s+x.cantidad,0);return{...o,cantidad:o.cantidad-a}})),M=b(()=>v.value.map(o=>{const a=$.value.filter(s=>s.nombre===o.nombre).reduce((s,x)=>s+x.cantidad,0);return{...o,cantidad:o.cantidad-a}})),t=_({masa:"",relleno:"",cantidad:1,sucursal_id:d.auth.user.sucursal_id}),n=o=>{const a={pastes:"bola","empanadas saladas":"salada","empanadas dulces":"dulce"},s=d.inventario.find(x=>(x.nombre.toLowerCase()===o.value.relleno.toLowerCase()||x.nombre.toLowerCase().startsWith(o.value.relleno.toLowerCase()))&&a[x.tipo.toLowerCase()]);return s?a[s.tipo.toLowerCase()]:"bola"},m=()=>{const o=n(t).toLowerCase(),a=H.value.find(x=>x.nombre.toLowerCase()===o),s=M.value.find(x=>x.nombre.toLowerCase()===t.value.relleno.toLowerCase());if(!a||a.cantidad<=0){z.fire({icon:"error",title:"No hay suficiente masa disponible"});return}if(a&&s&&t.value.cantidad<=D.value){a.cantidad-=t.value.cantidad,s.cantidad-=t.value.cantidad;const x=i.value.find(se=>se.nombre===s.nombre);x?x.cantidad+=t.value.cantidad:te({id:Date.now(),masa:a.nombre,nombre:s.nombre,cantidad:t.value.cantidad,sucursal_id:d.auth.user.sucursal_id}),t.value={masa:"",relleno:"",cantidad:1,sucursal_id:d.auth.user.sucursal_id}}},D=b(()=>{const o=M.value.find(a=>a.nombre===t.value.relleno);return o?o.cantidad:1}),O=b(()=>t.value.relleno&&t.value.cantidad>0&&t.value.cantidad<=D.value),U=_("00:00"),ee=o=>{if(o<=0)return"00:00";const a=Math.floor(o/1e3),s=Math.floor(a/60),x=a%60;return`${String(s).padStart(2,"0")}:${String(x).padStart(2,"0")}`},te=o=>{i.value.push(o)},ae=o=>{i.value=i.value.filter(a=>a.id!==o)},W=()=>{const o=y.value,a=o-Date.now();if(a<=0){P();return}p.value=h.value-a,k=setInterval(()=>{const s=o-Date.now();p.value=h.value-s,U.value=ee(s),console.log("tiempo transcurrido: ",U.value),s<=0&&P()},1e3)};return j(()=>{if(clearInterval(k),w.value){const o=Date.now()-c.value;y.value=Date.now()+(h.value-o),W()}}),(o,a)=>(r(),l(E,null,[e("div",Ne,[e("div",Ie,[a[4]||(a[4]=e("h2",{class:"text-xl font-semibold mb-4"},"Ingredientes Disponibles",-1)),e("div",je,[a[2]||(a[2]=e("h3",{class:"text-lg font-medium mb-2"},"Masas",-1)),e("ul",Ae,[(r(!0),l(E,null,S(H.value,s=>(r(),l("li",{key:s.id,class:"flex justify-between"},[e("span",Re,u(s.nombre),1),e("span",Oe,u(s.cantidad)+" unidades",1)]))),128))])]),e("div",null,[a[3]||(a[3]=e("h3",{class:"text-lg font-medium mb-2"},"Rellenos",-1)),e("ul",Ue,[(r(!0),l(E,null,S(M.value,s=>(r(),l("li",{key:s.id,class:"flex justify-between"},[e("span",null,u(s.nombre),1),e("span",We,u(s.cantidad)+" unidades",1)]))),128))])])]),e("div",Ge,[a[8]||(a[8]=e("h2",{class:"text-xl font-semibold mb-4"},"Crear Nuevo Paste/Empanada",-1)),e("form",{onSubmit:re(m,["prevent"]),class:"space-y-4"},[e("div",null,[a[6]||(a[6]=e("label",{for:"relleno",class:"block text-sm font-medium text-gray-700"},"Seleccionar Relleno",-1)),A(e("select",{"onUpdate:modelValue":a[0]||(a[0]=s=>t.value.relleno=s),id:"relleno",required:"",class:"mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm rounded-md"},[a[5]||(a[5]=e("option",{value:""},"Seleccione un relleno",-1)),(r(!0),l(E,null,S(v.value,s=>(r(),l("option",{key:s.id,value:s.nombre},u(s.nombre),9,Je))),128))],512),[[X,t.value.relleno]])]),e("div",null,[a[7]||(a[7]=e("label",{for:"cantidad",class:"block text-sm font-medium text-gray-700"},"Cantidad",-1)),A(e("input",{"onUpdate:modelValue":a[1]||(a[1]=s=>t.value.cantidad=s),type:"number",id:"cantidad",required:"",min:"1",max:D.value,class:"mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm"},null,8,qe),[[K,t.value.cantidad,void 0,{number:!0}]])]),e("button",{type:"submit",disabled:!O.value,class:"w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"}," Asignar Unidades ",8,Ze)],32)])]),e("div",Qe,[a[11]||(a[11]=e("h2",{class:"text-xl font-semibold mb-4"},"Horno",-1)),w.value?(r(),l("div",Xe,[a[9]||(a[9]=e("p",{class:"text-lg mb-2"},"Horneando grupo de pastes:",-1)),e("ul",null,[(r(!0),l(E,null,S($.value,s=>(r(),l("li",{key:s.nombre},u(s.cantidad)+" "+u(s.nombre)+" - masa "+u(s.masa),1))),128))]),e("div",Ke,[e("div",{class:"bg-orange-600 h-2.5 rounded-full transition-all duration-100",style:le({width:`${p.value/h.value*100}%`})},null,4)]),e("p",null,"Tiempo restante: "+u(U.value),1)])):(r(),l("div",Ye,[a[10]||(a[10]=e("p",{class:"text-lg mb-4"},"El horno está disponible",-1)),e("button",{onClick:L,disabled:!i.value.length,class:"w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"}," Iniciar Horneado de Grupo ",8,et)]))]),e("div",tt,[a[12]||(a[12]=e("h2",{class:"text-xl font-semibold mb-4"},"Grupos de Pastes por Hornear",-1)),i.value.length===0?(r(),l("div",at," No hay grupos de pastes en la cola de horneado ")):(r(),l("ul",st,[(r(!0),l(E,null,S(i.value,s=>(r(),l("li",{key:s.id,class:"py-4 flex justify-between items-center"},[e("span",null,u(s.cantidad)+" "+u(s.nombre)+" - masa "+u(s.masa),1),e("button",{onClick:x=>ae(s.id),class:"bg-red-600 text-white hover:bg-red-800 rounded-lg px-2 py-1 focus:outline-none focus:underline"}," Cancelar ",8,ot)]))),128))]))])],64))}},rt={class:"container mx-auto p-4"},lt={class:"sm:flex justify-between flex-row text-sm"},it={class:"flex gap-3 sm:flex-row flex-col"},dt={class:"text-lg font-bold text-center text-yellow-800"},ut={class:"animate-pulse"},ct={key:0,class:"mt-2"},mt={class:"font-bold"},pt={class:"text-lg font-bold text-center text-blue-800"},vt={class:"animate-pulse"},ft={key:0,class:"mt-2"},bt={class:"font-bold"},ht={class:"mt-6"},gt={__name:"Hornear",setup(B){const d=_(!1),C=_(!1);function w(){d.value=!d.value}function $(){C.value=!C.value}const h=R(),p=b(()=>h.props.inventario||[]),i=b(()=>h.props.estimacionesHoy||[]),c=_(y());function y(){const t=new Date;return{day:["Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado"][t.getDay()].toLowerCase(),hour:t.getHours(),minutes:t.getMinutes()}}j(()=>{setInterval(()=>{c.value=y()},6e4)});function k(t){const[n,m]=t.toLowerCase().split(" ");let[D,O]=n.split(":").map(Number);return m==="pm"&&D!==12?D+=12:m==="am"&&D===12&&(D=0),{hours:D,minutes:O}}const L=b(()=>p.value.filter(t=>{var n;return["pastes","empanadas dulces","empanadas saladas"].includes((n=t.tipo)==null?void 0:n.toLowerCase())})),z=b(()=>!i.value.length||!L.value.length?[]:i.value.map(t=>{const n=L.value.find(D=>D.id===t.inventario_id);if(!n)return null;const{hours:m}=k(t.hora);return{id:`${n.id}-${t.hora}`,nombre:n.nombre,estimado:t.cantidad,existente:n.cantidad,diferencia:n.cantidad-t.cantidad,hora:t.hora,dia:t.dia.toLowerCase(),horaEnNumero:m}}).filter(Boolean).filter((t,n,m)=>n===m.findIndex(D=>D.id===t.id))),P=b(()=>z.value.filter(t=>t.dia===c.value.day));b(()=>P.value.filter(t=>t.diferencia<0)),b(()=>P.value.filter(t=>t.diferencia>0));const F=b(()=>P.value.filter(t=>{const n=c.value.hour;return Math.abs(t.horaEnNumero-n)<=1})),f=b(()=>F.value.filter(t=>t.diferencia<0)),g=_(null);j(()=>{let t=!1;f.value.forEach(n=>{Math.abs(n.diferencia)>3&&!t&&(v(),t=!0),Math.abs(n.diferencia)>3&&H("warning",`Faltan ${Math.abs(n.diferencia)} unidades para las ${n.hora}`)}),clearInterval(g.value),g.value=setInterval(()=>{let n=!1;f.value.forEach(m=>{Math.abs(m.diferencia)>3&&!n&&(v(),n=!0),Math.abs(m.diferencia)>3&&H("warning",`Faltan ${Math.abs(m.diferencia)} unidades para las ${m.hora}`)})},10*60*1e3)});const v=()=>{new Audio("/sound/videoplayback.mp3").play().catch(n=>console.error("Error al reproducir el sonido:",n))},H=(t,n)=>{T.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:3e3,timerProgressBar:!0,customClass:"no-print",didOpen:m=>{m.addEventListener("mouseenter",T.stopTimer),m.addEventListener("mouseleave",T.resumeTimer)}}).fire({icon:t,title:n})},M=b(()=>F.value.filter(t=>t.diferencia>0));return(t,n)=>(r(),l("div",rt,[e("div",lt,[n[4]||(n[4]=e("h1",{class:"text-2xl font-bold mb-4"},"Sistema de Horneado",-1)),e("div",it,[e("div",{class:"p-4 border rounded shadow bg-yellow-100 mb-4 size-fit cursor-pointer",onClick:w},[e("h2",dt,[n[0]||(n[0]=V(" ⚠️ Faltantes ")),e("span",ut,"("+u(f.value.length)+")",1),n[1]||(n[1]=V(" ⚠️ "))]),d.value?(r(),l("ul",ct,[(r(!0),l(E,null,S(f.value,m=>(r(),l("li",{key:m.id,class:"mb-2"},[e("span",null,[e("span",mt,u(m.nombre),1),V(": Faltan "+u(Math.abs(m.diferencia))+" unidades para las "+u(m.hora),1)])]))),128))])):I("",!0)]),e("div",{class:"p-4 border rounded shadow bg-blue-100 mb-4 size-fit cursor-pointer",onClick:$},[e("h2",pt,[n[2]||(n[2]=V(" ℹ️ Excedentes ")),e("span",vt,"("+u(M.value.length)+")",1),n[3]||(n[3]=V(" ℹ️ "))]),C.value?(r(),l("ul",ft,[(r(!0),l(E,null,S(M.value,m=>(r(),l("li",{key:m.id,class:"mb-2"},[e("span",null,[e("span",bt,u(m.nombre),1),V(": Hay "+u(m.diferencia)+" unidades extra para las "+u(m.hora),1)])]))),128))])):I("",!0)])])]),e("div",ht,[N(nt),N(ke),N(Be)])]))}},xt={class:"py-6"},_t={class:"max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 rounded-xl overflow-hidden"},yt={class:"flex justify-between p-6 lg:p-8 bg-white border-b border-gray-200 rounded-2xl"},Dt={__name:"index",setup(B){return(d,C)=>(r(),ie(oe,{title:"Tablero principal"},{default:de(()=>[e("div",xt,[e("div",_t,[e("div",yt,[N(gt)])])])]),_:1}))}};export{Dt as default};
