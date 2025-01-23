import{_ as I}from"./AppLayout-Dtd3Rm2M.js";import{Q as B,d as w,l as g,o as r,e as u,a as l,F as _,h as y,n as L,t as n,j as P,m,s as v,f as p,z as T,g as x,x as U,u as R,c as G,w as O,b as Q}from"./app-CIHGODiB.js";import{S as h}from"./sweetalert2.esm.all-BGf-Fe8G.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";const q={class:"relative px-10 py-10 bg-white shadow-lg sm:rounded-3xl sm:p-20"},J={class:"mb-6 flex justify-center"},K=["onClick"],W={class:"flex gap-5 flex-col sm:flex-row"},X={class:"text-xl font-semibold mb-4"},Y={key:0},Z={key:0,class:"text-red-500 text-sm"},ee={key:1,class:"text-red-500 text-sm"},se={key:2,class:"text-red-500 text-sm"},le=["value"],oe={key:3,class:"text-red-500 text-sm"},te=["value"],ae={key:4,class:"text-red-500 text-sm"},re={key:1,class:"flex flex-col"},ue={key:0,class:"text-red-500 text-sm"},ne={key:1,class:"text-red-500 text-sm"},ie={key:2,class:"text-red-500 text-sm"},de={key:3,class:"text-red-500 text-sm"},ce={class:"flex justify-end"},pe={type:"submit",class:"bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 transition-colors duration-200 ease-in-out"},me={class:"mb-8 w-full sm:w-1/2"},ve={class:"text-xl font-semibold mb-4"},xe={class:"space-y-4"},fe={class:"flex justify-between items-center"},be={class:"font-semibold"},we={key:0,class:"text-sm text-gray-600"},ge={key:1,class:"text-sm text-gray-600 flex justify-between"},_e=["onClick"],ye=["onClick"],he={__name:"Personal",setup($){const{props:i}=B();console.log(i);const C=w(i.sucursales),V=[{label:"Usuarios",value:"users"},{label:"Sucursales",value:"sucursales"}],d=w("users"),t=w({id:null,id_user:null,name:"",email:"",password:"",sucursal_id:"",role:"",nombre:"",direccion:"",telefono:""}),M=g(()=>d.value==="users"?"Usuarios":"Sucursales"),z=g(()=>d.value==="users"?"Usuario":"Sucursal"),a=w({}),N=g(()=>d.value==="users"?i.users:i.sucursales),E=g(()=>i.roles.filter(o=>["admin","trabajador"].includes(o.name))),j=o=>o.name||o.email||o.nombre,S=o=>{if(!i.sucursales||!i.sucursalSession)return null;const s=i.sucursales.find(e=>e.id===o);if(s){const e=i.sucursalSession.find(c=>c.sucursal_id===s.id);if(e)return e}return null},A=()=>{const o=!!t.value.id,s=o?`/${d.value}/${t.value.id}`:`/${d.value}`;U[o?"put":"post"](s,t.value,{preserveScroll:!0,onSuccess:c=>{f.fire({icon:"success",title:o?"Actualizado correctamente":"Agregado correctamente"}),d.value==="users"?i.users=c.props.users:(i.sucursales=c.props.sucursales,i.sucursalSession=c.props.sucursalSession),k()},onError:c=>{f.fire({icon:"error",title:"Hubo un problema al procesar la solicitud"}),console.log(c),a.value=c,console.log(a.value)}})},F=o=>{d.value=o,k()},f=h.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:1500,timerProgressBar:!0,didOpen:o=>{o.onmouseenter=h.stopTimer,o.onmouseleave=h.resumeTimer}}),H=(o,s)=>{var e,c,b;t.value={id:o.id,id_user:((e=S(s))==null?void 0:e.id)||null,name:o.name||"",email:o.email||((c=S(s))==null?void 0:c.email)||"",sucursal_id:o.sucursal_id||"",role:((b=o.roles)==null?void 0:b[0])||"",nombre:o.nombre||"",direccion:o.direccion||"",telefono:o.telefono||"",password:""}},D=o=>{h.fire({title:"¿Estás seguro?",text:"¡No podrás revertir esta acción!",icon:"warning",showCancelButton:!0,confirmButtonColor:"#d33",cancelButtonColor:"#3085d6",confirmButtonText:"Sí, eliminarlo",cancelButtonText:"Cancelar"}).then(s=>{s.isConfirmed&&U.delete(`/${d.value}/${o}`,{preserveScroll:!0,onSuccess:e=>{console.log("Eliminado el id "+d.value+" - "+o),f.fire({icon:"success",title:"Eliminado correctamente"}),d.value==="users"?i.users=e.props.users:(i.sucursales=e.props.sucursales,i.sucursalSession=e.props.sucursalSession),a.value={},console.log(e)},onError:e=>{console.log("Error eliminado el id "+d.value+" - "+o),f.fire({icon:"error",title:a.value=e.error||"Hubo un problema al eliminar el elemento"}),a.value=e,console.log(e)}})})},k=()=>{t.value={id:null,id_user:null,name:"",email:"",password:"",sucursal_id:"",role:"",nombre:"",direccion:"",telefono:""},a.value={}};return(o,s)=>(r(),u("div",q,[s[19]||(s[19]=l("h1",{class:"text-3xl font-semibold mb-6 text-center"},"Gestión de Personal",-1)),l("div",J,[(r(),u(_,null,y(V,e=>l("button",{key:e.value,onClick:c=>F(e.value),class:L(["px-4 py-2 mx-1 rounded-full transition-colors duration-200 ease-in-out",d.value===e.value?"bg-orange-500 text-white":"bg-gray-200 hover:bg-gray-300"])},n(e.label),11,K)),64))]),l("div",W,[l("form",{onSubmit:P(A,["prevent"]),class:"space-y-4 w-full sm:w-1/2"},[l("h2",X,n(t.value.id?"Editar":"Agregar")+" "+n(z.value),1),d.value==="users"?(r(),u("div",Y,[m(l("input",{"onUpdate:modelValue":s[0]||(s[0]=e=>t.value.name=e),placeholder:"Nombre",class:"w-full px-3 py-2 border rounded"},null,512),[[v,t.value.name]]),a.value.name?(r(),u("span",Z,n(a.value.name),1)):p("",!0),m(l("input",{"onUpdate:modelValue":s[1]||(s[1]=e=>t.value.email=e),type:"text",placeholder:"Matricula",class:"mt-2 w-full px-3 py-2 border rounded"},null,512),[[v,t.value.email]]),a.value.email?(r(),u("span",ee,n(a.value.email),1)):p("",!0),m(l("input",{"onUpdate:modelValue":s[2]||(s[2]=e=>t.value.password=e),type:"password",placeholder:"Contraseña",class:"mt-2 w-full px-3 py-2 border rounded"},null,512),[[v,t.value.password]]),a.value.password?(r(),u("span",se,n(a.value.password),1)):p("",!0),m(l("select",{"onUpdate:modelValue":s[3]||(s[3]=e=>t.value.sucursal_id=e),class:"mt-2 w-full px-3 py-2 border rounded"},[s[10]||(s[10]=l("option",{value:""},"Seleccionar Sucursal",-1)),(r(!0),u(_,null,y(C.value,e=>(r(),u("option",{key:e.id,value:e.id},n(e.nombre),9,le))),128))],512),[[T,t.value.sucursal_id]]),a.value.sucursal_id?(r(),u("span",oe,n(a.value.sucursal_id),1)):p("",!0),m(l("select",{"onUpdate:modelValue":s[4]||(s[4]=e=>t.value.role=e),class:"mt-2 w-full px-3 py-2 border rounded"},[s[11]||(s[11]=l("option",{value:""},"Seleccionar Rol",-1)),(r(!0),u(_,null,y(E.value,e=>(r(),u("option",{key:e.id,value:e.name},n(e.name),9,te))),128))],512),[[T,t.value.role]]),a.value.role?(r(),u("span",ae,n(a.value.role),1)):p("",!0)])):d.value==="sucursales"?(r(),u("div",re,[m(l("input",{"onUpdate:modelValue":s[5]||(s[5]=e=>t.value.nombre=e),placeholder:"Nombre de Sucursal",class:"w-full px-3 py-2 border rounded"},null,512),[[v,t.value.nombre]]),a.value.nombre?(r(),u("span",ue,n(a.value.nombre),1)):p("",!0),m(l("input",{"onUpdate:modelValue":s[6]||(s[6]=e=>t.value.direccion=e),placeholder:"Dirección",class:"mt-2 w-full px-3 py-2 border rounded"},null,512),[[v,t.value.direccion]]),a.value.direccion?(r(),u("span",ne,n(a.value.direccion),1)):p("",!0),m(l("input",{"onUpdate:modelValue":s[7]||(s[7]=e=>t.value.telefono=e),placeholder:"Teléfono",class:"mt-2 w-full px-3 py-2 border rounded"},null,512),[[v,t.value.telefono]]),s[12]||(s[12]=l("label",{for:"credenciales",class:"pt-4 font-semibold"},"Credenciales de inicio de sesión",-1)),m(l("input",{"onUpdate:modelValue":s[8]||(s[8]=e=>t.value.email=e),placeholder:"Matricula",class:"mt-2 w-full px-3 py-2 border rounded"},null,512),[[v,t.value.email]]),a.value.email?(r(),u("span",ie,n(a.value.email),1)):p("",!0),m(l("input",{"onUpdate:modelValue":s[9]||(s[9]=e=>t.value.password=e),placeholder:"Contraseña",class:"mt-2 w-full px-3 py-2 border rounded"},null,512),[[v,t.value.password]]),a.value.password?(r(),u("span",de,n(a.value.password),1)):p("",!0)])):p("",!0),l("div",ce,[l("button",pe,n(t.value.id?"Actualizar":"Agregar"),1),t.value.id?(r(),u("button",{key:0,onClick:k,type:"button",class:"ml-2 bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition-colors duration-200 ease-in-out"}," Cancelar ")):p("",!0)])],32),l("div",me,[l("h2",ve,n(M.value),1),l("div",xe,[(r(!0),u(_,null,y(N.value,e=>{var c;return r(),u("div",{key:e.id,class:"bg-gray-50 p-4 rounded-lg shadow hover:shadow-md transition-shadow duration-200 ease-in-out"},[l("div",fe,[l("div",null,[l("h3",be,"Nombre: "+n(j(e)),1),d.value==="users"?(r(),u("p",we,[x(" Matricula: "+n(e.email)+" ",1),s[13]||(s[13]=l("br",null,null,-1)),x(" Rol: "+n(e.roles[0]||"Sin rol"),1)])):p("",!0),d.value==="sucursales"?(r(),u("div",ge,[l("p",null,[x(n(e.direccion)+" - "+n(e.telefono||"Sin teléfono")+" ",1),s[14]||(s[14]=l("br",null,null,-1)),s[15]||(s[15]=x(" Credenciales ")),s[16]||(s[16]=l("br",null,null,-1)),x(" Matricula: "+n((c=S(e.id))==null?void 0:c.email),1)])])):p("",!0)]),l("div",null,[l("button",{onClick:b=>H(e,e.id),class:"text-orange-500 hover:text-orange-700 mr-2"},s[17]||(s[17]=[l("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-5 w-5",viewBox:"0 0 20 20",fill:"currentColor"},[l("path",{d:"M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"})],-1)]),8,_e),l("button",{onClick:b=>D(e.id),class:"text-red-500 hover:text-red-700"},s[18]||(s[18]=[l("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-5 w-5",viewBox:"0 0 20 20",fill:"currentColor"},[l("path",{"fill-rule":"evenodd",d:"M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z","clip-rule":"evenodd"})],-1)]),8,ye)])])])}),128))])])])]))}},Se={class:"py-6"},ke={class:"max-w-7xl mx-auto sm:px-6 lg:px-8 p-2 md:p-8"},Ce={class:"bg-white overflow-hidden shadow-xl rounded-xl"},$e={__name:"index",setup($){const{props:i}=B();return console.log(i.user.roles[0]),(C,V)=>R(i).user.roles[0]!=="trabajador"?(r(),G(I,{key:0,title:"Tablero principal"},{default:O(()=>[l("div",Se,[l("div",ke,[l("div",Ce,[Q(he)])])])]),_:1})):p("",!0)}};export{$e as default};
