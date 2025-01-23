import{_ as M}from"./AppLayout-Dtd3Rm2M.js";import{Q as V,T as b,d as g,q as $,o as d,e as i,a as e,j as k,m,s as x,u as a,t as l,f as h,F as C,h as E,n as T,x as D,c as U,w as B,b as N}from"./app-CIHGODiB.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";const q={class:"min-h-max h-[80vh] gap-5 m-auto md:flex-row flex flex-col items-center p-4 md:p-8 justify-center"},z={class:"flex flex-col w-full"},L={class:"w-full flex gap-5 flex-col md:flex-row items-start"},O={class:"md:w-4/12 h-fit w-full border p-10 rounded-xl shadow-xl"},R={class:"rounded-md shadow-sm -space-y-px"},A={key:0,class:"mt-4 text-sm text-green-600"},H={key:1,class:"mt-4 text-sm text-red-600"},P={key:0,class:"md:w-8/12 w-full h-fit"},Q={class:"flex md:space-x-4 flex-col md:flex-row"},G={class:"flex-1 p-2 md:p-0"},J={class:"flex-1 p-2 md:p-0"},K={class:"overflow-x-auto"},W={class:"min-w-full divide-y divide-gray-200"},X={class:"bg-white divide-y divide-gray-200"},Y={class:"px-6 py-4 whitespace-nowrap"},Z={class:"px-6 py-4 whitespace-nowrap"},j={class:"px-6 py-4 whitespace-nowrap"},ee={class:"px-6 py-4 whitespace-nowrap"},te={class:"px-6 py-4 whitespace-nowrap"},se={class:"px-6 py-4 whitespace-nowrap"},oe={__name:"Checador",setup(S){const{props:c}=V(),r=b({email:"",password:""}),n=b({startDate:"",endDate:""}),f=g(c.checkIns),p=g(""),u=g("");$(()=>{c.filters&&(n.startDate=c.filters.startDate||"",n.endDate=c.filters.endDate||"")});const w=o=>o?new Date(o).toLocaleTimeString("es-ES",{hour:"2-digit",minute:"2-digit"}):"-",F=()=>{D.post("/checkInOut",{email:r.email,password:r.password},{preserveState:!0,preserveScroll:!0,onSuccess:o=>{p.value=o.props.flash.success,u.value="",r.reset("password"),f.value=o.props.checkIns},onError:o=>{u.value=Object.values(o)[0],p.value=""}})},I=()=>{D.post("/search-check-ins",n.data(),{preserveState:!0,preserveScroll:!0,only:["checkIns"],onSuccess:o=>{f.value=o.props.checkIns}})};return(o,t)=>(d(),i("div",q,[e("div",z,[e("div",L,[e("div",O,[t[7]||(t[7]=e("div",null,[e("h2",{class:"mt-6 text-center text-3xl font-extrabold text-gray-900"}," Registro de Entrada/Salida ")],-1)),e("form",{class:"mt-8 space-y-6",onSubmit:k(F,["prevent"])},[e("div",R,[e("div",null,[t[4]||(t[4]=e("label",{for:"email",class:"sr-only"},"Matricula de Usuario",-1)),m(e("input",{id:"email",name:"email",type:"text",required:"",class:"appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 sm:text-sm",placeholder:"Matricula de Usuario","onUpdate:modelValue":t[0]||(t[0]=s=>a(r).email=s)},null,512),[[x,a(r).email]])]),e("div",null,[t[5]||(t[5]=e("label",{for:"password",class:"sr-only"},"Contraseña",-1)),m(e("input",{id:"password",name:"password",type:"password",required:"",class:"appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 sm:text-sm",placeholder:"Contraseña","onUpdate:modelValue":t[1]||(t[1]=s=>a(r).password=s)},null,512),[[x,a(r).password]])])]),t[6]||(t[6]=e("div",null,[e("button",{type:"submit",class:"group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"}," Registrar ")],-1))],32),p.value?(d(),i("div",A,l(p.value),1)):h("",!0),u.value?(d(),i("div",H,l(u.value),1)):h("",!0)]),o.$page.props.user.roles!=="trabajador"?(d(),i("div",P,[e("form",{class:"mb-4 p-4 border rounded-xl shadow-md",onSubmit:k(I,["prevent"])},[e("div",Q,[e("div",G,[t[8]||(t[8]=e("label",{for:"startDate",class:"block text-sm font-medium text-gray-700"},"Fecha de Inicio",-1)),m(e("input",{id:"startDate",type:"date","onUpdate:modelValue":t[2]||(t[2]=s=>a(n).startDate=s),class:"mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"},null,512),[[x,a(n).startDate]])]),e("div",J,[t[9]||(t[9]=e("label",{for:"endDate",class:"block text-sm font-medium text-gray-700"},"Fecha de Fin",-1)),m(e("input",{id:"endDate",type:"date","onUpdate:modelValue":t[3]||(t[3]=s=>a(n).endDate=s),class:"mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"},null,512),[[x,a(n).endDate]])]),t[10]||(t[10]=e("div",{class:"flex-1 flex items-end p-2 md:p-0"},[e("button",{type:"submit",class:"w-full py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"}," Buscar ")],-1))])],32),e("div",K,[e("table",W,[t[11]||(t[11]=e("thead",{class:"bg-gray-50"},[e("tr",null,[e("th",{scope:"col",class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"}," ID "),e("th",{scope:"col",class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"}," Nombre "),e("th",{scope:"col",class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"}," Estado "),e("th",{scope:"col",class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"}," Entrada "),e("th",{scope:"col",class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"}," Salida "),e("th",{scope:"col",class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"}," Horas Trabajadas ")])],-1)),e("tbody",X,[(d(!0),i(C,null,E(f.value,s=>{var y,v,_;return d(),i("tr",{key:s.id},[e("td",Y,l(s.user.email),1),e("td",Z,l(s.user.name),1),e("td",j,[e("span",{class:T([(s.estado||"Ausente")==="Presente"?"bg-green-100 text-green-800":"bg-red-100 text-red-800","px-2 inline-flex text-xs leading-5 font-semibold rounded-full"])},l(s.estado),3)]),e("td",ee,l(((y=s.check_in)==null?void 0:y.length)>0?w(s.check_in):"-"),1),e("td",te,l(((v=s.check_out)==null?void 0:v.length)>0&&s.check_out?w(s.check_out):"-"),1),e("td",se,l(((_=s.check_out)==null?void 0:_.length)>0?(s.horas_trabajadas/60).toFixed(2):"-"),1)])}),128))])])])])):h("",!0)])])]))}},re={class:"py-6 px-2"},ae={class:"max-w-7xl mx-auto sm:px-6 lg:px-8 py-10"},le={class:"bg-white overflow-hidden shadow-xl rounded-lg"},ne={class:"overflow-hidden"},pe={__name:"index",setup(S){return(c,r)=>(d(),U(M,{title:"Tablero principal"},{default:B(()=>[e("div",re,[e("div",ae,[e("div",le,[e("div",ne,[N(oe)])])])])]),_:1}))}};export{pe as default};
