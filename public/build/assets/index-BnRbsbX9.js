import{S as C,_ as J}from"./AppLayout-d2O9Z-9c.js";import{o as l,e as o,a as s,b as E,n as F,u as j,t as a,F as g,h as k,f as r,Q as P,d as h,p as f,c as A,k as K,j as v,v as _,l as D,q as W,z,w as X}from"./app-BltF6xnM.js";import{c as Y}from"./createLucideIcon-hJ1H92Bo.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";/**
 * @license lucide-vue-next v0.473.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Z=Y("CircleAlertIcon",[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["line",{x1:"12",x2:"12",y1:"8",y2:"12",key:"1pkeuh"}],["line",{x1:"12",x2:"12.01",y1:"16",y2:"16",key:"4dfq90"}]]),ee={key:0,class:"card grid gap-2"},se={class:"flex items-center gap-2"},te={class:"font-bold text-xl"},le={class:"table-auto w-full border"},I={__name:"CardContratos",props:{contratos:Array,title:String,color:String},setup(x){const c=y=>y?new Date(y).toLocaleDateString():"N/A";return(y,b)=>x.contratos.length>0?(l(),o("div",ee,[s("span",se,[E(j(Z),{size:32,class:F(["animate-pulse",x.color==="red"?"text-red-500":"text-yellow-500"])},null,8,["class"]),s("h2",te,a(x.title),1)]),s("table",le,[b[0]||(b[0]=s("thead",{class:"bg-gray-200"},[s("tr",{class:"text-left"},[s("th",null,"Nombre"),s("th",null,"Apellido Paterno"),s("th",null,"Apellido Materno"),s("th",null,"Inicio del Contrato"),s("th",null,"Fin del Contrato")])],-1)),s("tbody",null,[(l(!0),o(g,null,k(x.contratos,m=>(l(),o("tr",{key:m.id},[s("td",null,a(m.name),1),s("td",null,a(m.apellido_p),1),s("td",null,a(m.apellido_m),1),s("td",null,a(c(m.inicio_contrato)),1),s("td",null,a(c(m.fin_contrato)),1)]))),128))])])])):r("",!0)}},oe={class:"relative px-10 py-10 bg-white shadow-lg sm:rounded-3xl sm:p-20"},re={key:0,class:"container my-10 grid gap-2"},ae={class:"mb-6 flex justify-center"},ne=["onClick"],ue={class:"flex justify-end mb-4"},ie={class:"mb-8 overflow-auto"},de={class:"text-xl font-semibold mb-4"},ce={class:"min-w-full bg-white border border-gray-200"},pe={key:0,class:"px-4 py-2 border-b"},ve={key:1,class:"px-4 py-2 border-b"},_e={key:2,class:"px-4 py-2 border-b"},me={key:3,class:"px-4 py-2 border-b"},be={key:4,class:"px-4 py-2 border-b"},xe={key:5,class:"px-4 py-2 border-b"},ye={key:6,class:"px-4 py-2 border-b"},fe={key:7,class:"px-4 py-2 border-b"},he={key:8,class:"px-4 py-2 border-b"},ge={key:9,class:"px-4 py-2 border-b"},ke={key:10,class:"px-4 py-2 border-b"},we={key:11,class:"px-4 py-2 border-b"},Ce={key:12,class:"px-4 py-2 border-b"},Se={key:0,class:"px-4 py-2 border-b"},Ve={key:1,class:"px-4 py-2 border-b"},Ae={key:2,class:"px-4 py-2 border-b"},Ue={key:3,class:"px-4 py-2 border-b"},Me={key:4,class:"px-4 py-2 border-b"},$e={key:5,class:"px-4 py-2 border-b"},Te={key:6,class:"px-4 py-2 border-b"},Ne={key:7,class:"px-4 py-2 border-b"},Be={key:8,class:"px-4 py-2 border-b"},De={key:0,class:"bg-green-500 text-white px-2 py-1 rounded-full"},ze={key:1,class:"bg-red-500 text-white px-2 py-1 rounded-full"},Ie={key:9,class:"px-4 py-2 border-b"},Ee={key:10,class:"px-4 py-2 border-b"},Fe={key:11,class:"px-4 py-2 border-b"},je={key:12,class:"px-4 py-2 border-b"},Pe={class:"px-4 py-2 border-b"},Le=["onClick"],He=["onClick"],Re={key:0,class:"fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center"},qe={class:"bg-white p-6 rounded-lg w-1/2"},Oe={class:"text-xl font-semibold mb-4"},Ge={key:0},Qe={key:0,class:"text-red-500 text-sm"},Je={key:1,class:"text-red-500 text-sm"},Ke={key:2,class:"text-red-500 text-sm"},We={key:3,class:"text-red-500 text-sm"},Xe={class:"flex w-full space-x-4 mt-2"},Ye={class:"w-full"},Ze={key:0,class:"text-red-500 text-sm"},es={class:"w-full"},ss={key:0,class:"text-red-500 text-sm"},ts={key:4,class:"text-red-500 text-sm"},ls={key:5,class:"text-red-500 text-sm"},os=["value"],rs={key:6,class:"text-red-500 text-sm"},as=["value"],ns={class:"flex items-center mt-2"},us={key:7,class:"text-red-500 text-sm"},is={key:1,class:"flex flex-col"},ds={key:0,class:"text-red-500 text-sm"},cs={key:1,class:"text-red-500 text-sm"},ps={key:2,class:"text-red-500 text-sm"},vs={key:3,class:"text-red-500 text-sm"},_s={key:4,class:"text-red-500 text-sm"},ms={class:"flex justify-end"},bs={type:"submit",class:"bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 transition-colors duration-200 ease-in-out"},xs={__name:"Personal",setup(x){const{props:c}=P(),y=h(c.sucursales),b=h(!1),m=[{label:"Usuarios",value:"users"},{label:"Sucursales",value:"sucursales"}],u=h("users"),n=h({id:null,id_user:null,name:"",apellido_p:"",apellido_m:"",inicio_contrato:"",fin_contrato:"",email:"",password:"",sucursal_id:"",role:"",nombre:"",direccion:"",telefono:"",active:!1}),L=f(()=>u.value==="users"?"Usuarios":"Sucursales"),U=f(()=>u.value==="users"?"Usuario":"Sucursal"),d=h({}),H=f(()=>u.value==="users"?c.users:c.sucursales.filter(i=>i.id!==0)),R=f(()=>c.roles.filter(i=>["admin","trabajador"].includes(i.name))),S=f(()=>{const i=new Date;return c.users.filter(t=>{if(t.fin_contrato===null)return!1;const e=new Date(t.fin_contrato);return isNaN(e)?!1:e<i})}),V=f(()=>{const i=new Date,t=5*24*60*60*1e3;return c.users.filter(e=>{if(e.fin_contrato===null||e.active===0)return!1;const p=new Date(e.fin_contrato);if(isNaN(p))return!1;const B=p-i;return B>0&&B<=t})}),M=i=>{if(!c.sucursales||!c.sucursalSession)return null;const t=c.sucursales.find(e=>e.id===i);if(t){const e=c.sucursalSession.find(p=>p.sucursal_id===t.id);if(e)return e}return null},$=()=>{b.value=!0},T=()=>{b.value=!1,N()},q=()=>{const i=!!n.value.id,t=i?`/${u.value}/${n.value.id}`:`/${u.value}`;z[i?"put":"post"](t,n.value,{preserveScroll:!0,onSuccess:p=>{w.fire({icon:"success",title:i?"Actualizado correctamente":"Agregado correctamente"}),u.value==="users"?c.users=p.props.users:(c.sucursales=p.props.sucursales,c.sucursalSession=p.props.sucursalSession),T()},onError:p=>{w.fire({icon:"error",title:"Hubo un problema al procesar la solicitud"}),d.value=p}})},O=i=>{u.value=i,N()},w=C.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:1500,timerProgressBar:!0,didOpen:i=>{i.onmouseenter=C.stopTimer,i.onmouseleave=C.resumeTimer}}),G=i=>{var t,e,p;n.value={id:i.id,id_user:((t=M(i.id))==null?void 0:t.id)||null,name:i.name||"",apellido_p:i.apellido_p||"",apellido_m:i.apellido_m||"",tel:i.tel||"",email:i.email||((e=M(i.id))==null?void 0:e.email)||"",sucursal_id:i.sucursal_id||"",role:((p=i.roles)==null?void 0:p[0])||"",nombre:i.nombre||"",direccion:i.direccion||"",telefono:i.telefono||"",inicio_contrato:i.inicio_contrato||"",fin_contrato:i.fin_contrato||"",active:i.active||!1,password:""},$()},Q=i=>{C.fire({title:"¿Estás seguro?",text:"¡No podrás revertir esta acción!",icon:"warning",showCancelButton:!0,confirmButtonColor:"#d33",cancelButtonColor:"#3085d6",confirmButtonText:"Sí, eliminarlo",cancelButtonText:"Cancelar"}).then(t=>{t.isConfirmed&&z.delete(`/${u.value}/${i}`,{preserveScroll:!0,onSuccess:e=>{w.fire({icon:"success",title:"Eliminado correctamente"}),u.value==="users"?c.users=e.props.users:(c.sucursales=e.props.sucursales,c.sucursalSession=e.props.sucursalSession),d.value={}},onError:e=>{w.fire({icon:"error",title:d.value=e.error||"Hubo un problema al eliminar el elemento"}),d.value=e}})})},N=()=>{n.value={id:null,id_user:null,name:"",email:"",password:"",sucursal_id:"",role:"",nombre:"",direccion:"",telefono:""},d.value={}};return(i,t)=>(l(),o("div",oe,[t[25]||(t[25]=s("h1",{class:"text-3xl font-semibold mb-6 text-center"},"Gestión de Personal",-1)),s("div",null,[S.value.length>0||V.value.length>0?(l(),o("div",re,[V.value.length>0?(l(),A(I,{key:0,contratos:V.value,title:"Contratos por Vencer",color:"yellow"},null,8,["contratos"])):r("",!0),S.value.length>0?(l(),A(I,{key:1,contratos:S.value,title:"Contratos Vencidos",color:"red"},null,8,["contratos"])):r("",!0)])):r("",!0)]),s("div",ae,[(l(),o(g,null,k(m,e=>s("button",{key:e.value,onClick:p=>O(e.value),class:F(["px-4 py-2 mx-1 rounded-full transition-colors duration-200 ease-in-out",u.value===e.value?"bg-orange-500 text-white":"bg-gray-200 hover:bg-gray-300"])},a(e.label),11,ne)),64))]),s("div",ue,[s("button",{onClick:$,class:"bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 transition-colors duration-200 ease-in-out"}," Agregar "+a(U.value),1)]),s("div",ie,[s("h2",de,a(L.value),1),s("table",ce,[s("thead",null,[s("tr",null,[u.value==="users"?(l(),o("th",pe,"Nombre")):r("",!0),u.value==="users"?(l(),o("th",ve,"Apellido P.")):r("",!0),u.value==="users"?(l(),o("th",_e,"Apellido M.")):r("",!0),u.value==="users"?(l(),o("th",me,"Telefono")):r("",!0),u.value==="users"?(l(),o("th",be,"Usuario")):r("",!0),u.value==="users"?(l(),o("th",xe,"Inicio Contrato")):r("",!0),u.value==="users"?(l(),o("th",ye,"Fin Contrato")):r("",!0),u.value==="users"?(l(),o("th",fe,"Sucursal")):r("",!0),u.value==="users"?(l(),o("th",he,"Activo")):r("",!0),u.value==="users"?(l(),o("th",ge,"Rol")):r("",!0),u.value==="sucursales"?(l(),o("th",ke,"Nombre de Sucursal")):r("",!0),u.value==="sucursales"?(l(),o("th",we,"Dirección")):r("",!0),u.value==="sucursales"?(l(),o("th",Ce,"Teléfono")):r("",!0),t[16]||(t[16]=s("th",{class:"px-4 py-2 border-b"},"Acciones",-1))])]),s("tbody",null,[(l(!0),o(g,null,k(H.value,e=>(l(),o("tr",{key:e.id,class:"hover:bg-gray-50"},[u.value==="users"?(l(),o("td",Se,a(e.name),1)):r("",!0),u.value==="users"?(l(),o("td",Ve,a(e.apellido_p),1)):r("",!0),u.value==="users"?(l(),o("td",Ae,a(e.apellido_m),1)):r("",!0),u.value==="users"?(l(),o("td",Ue,a(e.tel),1)):r("",!0),u.value==="users"?(l(),o("td",Me,a(e.email),1)):r("",!0),u.value==="users"?(l(),o("td",$e,a(e.inicio_contrato),1)):r("",!0),u.value==="users"?(l(),o("td",Te,a(e.fin_contrato),1)):r("",!0),u.value==="users"?(l(),o("td",Ne,a(e.sucursal),1)):r("",!0),u.value==="users"?(l(),o("td",Be,[e.active?(l(),o("span",De,"Activo")):(l(),o("span",ze,"Inactivo"))])):r("",!0),u.value==="users"?(l(),o("td",Ie,a(e.roles[0]||"Sin rol"),1)):r("",!0),u.value==="sucursales"?(l(),o("td",Ee,a(e.nombre),1)):r("",!0),u.value==="sucursales"?(l(),o("td",Fe,a(e.direccion),1)):r("",!0),u.value==="sucursales"?(l(),o("td",je,a(e.telefono||"Sin teléfono"),1)):r("",!0),s("td",Pe,[s("button",{onClick:p=>G(e),class:"text-orange-500 hover:text-orange-700 mr-2"},t[17]||(t[17]=[s("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-5 w-5",viewBox:"0 0 20 20",fill:"currentColor"},[s("path",{d:"M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"})],-1)]),8,Le),s("button",{onClick:p=>Q(e.id),class:"text-red-500 hover:text-red-700"},t[18]||(t[18]=[s("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-5 w-5",viewBox:"0 0 20 20",fill:"currentColor"},[s("path",{"fill-rule":"evenodd",d:"M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z","clip-rule":"evenodd"})],-1)]),8,He)])]))),128))])])]),b.value?(l(),o("div",Re,[s("div",qe,[s("h2",Oe,a(n.value.id?"Editar":"Agregar")+" "+a(U.value),1),s("form",{onSubmit:K(q,["prevent"]),class:"space-y-4"},[u.value==="users"?(l(),o("div",Ge,[v(s("input",{"onUpdate:modelValue":t[0]||(t[0]=e=>n.value.name=e),placeholder:"Nombre",class:"w-full px-3 py-2 border rounded"},null,512),[[_,n.value.name]]),d.value.name?(l(),o("span",Qe,a(d.value.name),1)):r("",!0),v(s("input",{"onUpdate:modelValue":t[1]||(t[1]=e=>n.value.apellido_p=e),placeholder:"Apellido Paterno",class:"mt-2 w-full px-3 py-2 border rounded"},null,512),[[_,n.value.apellido_p]]),d.value.apellido_p?(l(),o("span",Je,a(d.value.apellido_p),1)):r("",!0),v(s("input",{"onUpdate:modelValue":t[2]||(t[2]=e=>n.value.apellido_m=e),placeholder:"Apellido Materno",class:"mt-2 w-full px-3 py-2 border rounded"},null,512),[[_,n.value.apellido_m]]),d.value.apellido_m?(l(),o("span",Ke,a(d.value.apellido_m),1)):r("",!0),v(s("input",{"onUpdate:modelValue":t[3]||(t[3]=e=>n.value.tel=e),placeholder:"Teléfono",class:"w-full px-3 py-2 border mt-2 rounded"},null,512),[[_,n.value.tel]]),d.value.tel?(l(),o("span",We,a(d.value.tel),1)):r("",!0),s("div",Xe,[s("div",Ye,[t[19]||(t[19]=s("label",{for:"inicio_contrato",class:"block text-sm font-medium text-gray-700"},"Inicio de Contrato",-1)),v(s("input",{"onUpdate:modelValue":t[4]||(t[4]=e=>n.value.inicio_contrato=e),type:"date",id:"inicio_contrato",class:"mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"},null,512),[[_,n.value.inicio_contrato]]),d.value.inicio_contrato?(l(),o("span",Ze,a(d.value.inicio_contrato),1)):r("",!0)]),s("div",es,[t[20]||(t[20]=s("label",{for:"fin_contrato",class:"block text-sm font-medium text-gray-700"},"Fin de Contrato",-1)),v(s("input",{"onUpdate:modelValue":t[5]||(t[5]=e=>n.value.fin_contrato=e),type:"date",id:"fin_contrato",class:"mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"},null,512),[[_,n.value.fin_contrato]]),d.value.fin_contrato?(l(),o("span",ss,a(d.value.fin_contrato),1)):r("",!0)])]),v(s("input",{"onUpdate:modelValue":t[6]||(t[6]=e=>n.value.email=e),type:"text",placeholder:"Matricula",class:"mt-2 w-full px-3 py-2 border rounded"},null,512),[[_,n.value.email]]),d.value.email?(l(),o("span",ts,a(d.value.email),1)):r("",!0),v(s("input",{"onUpdate:modelValue":t[7]||(t[7]=e=>n.value.password=e),type:"password",placeholder:"Contraseña",class:"mt-2 w-full px-3 py-2 border rounded"},null,512),[[_,n.value.password]]),d.value.password?(l(),o("span",ls,a(d.value.password),1)):r("",!0),v(s("select",{"onUpdate:modelValue":t[8]||(t[8]=e=>n.value.sucursal_id=e),class:"mt-2 w-full px-3 py-2 border rounded"},[t[21]||(t[21]=s("option",{value:""},"Seleccionar Sucursal",-1)),(l(!0),o(g,null,k(y.value,e=>(l(),o("option",{key:e.id,value:e.id},a(e.nombre),9,os))),128))],512),[[D,n.value.sucursal_id]]),d.value.sucursal_id?(l(),o("span",rs,a(d.value.sucursal_id),1)):r("",!0),v(s("select",{"onUpdate:modelValue":t[9]||(t[9]=e=>n.value.role=e),class:"mt-2 w-full px-3 py-2 border rounded"},[t[22]||(t[22]=s("option",{value:""},"Seleccionar Rol",-1)),(l(!0),o(g,null,k(R.value,e=>(l(),o("option",{key:e.id,value:e.name},a(e.name),9,as))),128))],512),[[D,n.value.role]]),s("div",ns,[v(s("input",{"onUpdate:modelValue":t[10]||(t[10]=e=>n.value.active=e),"true-value":1,"false-value":0,type:"checkbox",id:"active",class:"form-checkbox h-5 w-5 text-orange-500"},null,512),[[W,n.value.active]]),t[23]||(t[23]=s("label",{for:"active",class:"ml-2 text-sm font-medium text-gray-700"},"Activo",-1))]),d.value.role?(l(),o("span",us,a(d.value.role),1)):r("",!0)])):u.value==="sucursales"?(l(),o("div",is,[v(s("input",{"onUpdate:modelValue":t[11]||(t[11]=e=>n.value.nombre=e),placeholder:"Nombre de Sucursal",class:"w-full px-3 py-2 border rounded"},null,512),[[_,n.value.nombre]]),d.value.nombre?(l(),o("span",ds,a(d.value.nombre),1)):r("",!0),v(s("input",{"onUpdate:modelValue":t[12]||(t[12]=e=>n.value.direccion=e),placeholder:"Dirección",class:"mt-2 w-full px-3 py-2 border rounded"},null,512),[[_,n.value.direccion]]),d.value.direccion?(l(),o("span",cs,a(d.value.direccion),1)):r("",!0),v(s("input",{"onUpdate:modelValue":t[13]||(t[13]=e=>n.value.telefono=e),placeholder:"Teléfono",class:"mt-2 w-full px-3 py-2 border rounded"},null,512),[[_,n.value.telefono]]),d.value.telefono?(l(),o("span",ps,a(d.value.telefono),1)):r("",!0),t[24]||(t[24]=s("label",{for:"credenciales",class:"pt-4 font-semibold"},"Credenciales de inicio de sesión",-1)),v(s("input",{"onUpdate:modelValue":t[14]||(t[14]=e=>n.value.email=e),placeholder:"Matricula",class:"mt-2 w-full px-3 py-2 border rounded"},null,512),[[_,n.value.email]]),d.value.email?(l(),o("span",vs,a(d.value.email),1)):r("",!0),v(s("input",{"onUpdate:modelValue":t[15]||(t[15]=e=>n.value.password=e),placeholder:"Contraseña",class:"mt-2 w-full px-3 py-2 border rounded"},null,512),[[_,n.value.password]]),d.value.password?(l(),o("span",_s,a(d.value.password),1)):r("",!0)])):r("",!0),s("div",ms,[s("button",bs,a(n.value.id?"Actualizar":"Agregar"),1),s("button",{onClick:T,type:"button",class:"ml-2 bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition-colors duration-200 ease-in-out"}," Cancelar ")])],32)])])):r("",!0)]))}},ys={class:"py-6"},fs={class:"max-w-7xl mx-auto sm:px-6 lg:px-8 p-2 md:p-8"},hs={class:"bg-white overflow-hidden shadow-xl rounded-xl"},Ss={__name:"index",setup(x){const{props:c}=P();return console.log(c.user.roles[0]),(y,b)=>j(c).user.roles[0]!=="trabajador"?(l(),A(J,{key:0,title:"Tablero principal"},{default:X(()=>[s("div",ys,[s("div",fs,[s("div",hs,[E(xs)])])])]),_:1})):r("",!0)}};export{Ss as default};
