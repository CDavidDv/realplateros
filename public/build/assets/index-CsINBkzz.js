import{Q as B,d as f,B as I,k as E,o as m,e as b,a as e,t as a,i as S,l as i,q as c,y as $,C as N,F as V,h as T,s as x,c as U,w as A,b as D}from"./app-DKkY_VYG.js";import{S as p}from"./sweetalert2.esm.all-BGf-Fe8G.js";import{_ as P}from"./AppLayout-WmeeiV7u.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";const q={class:"container mx-auto p-8 md:p-10"},L={class:"flex flex-col md:flex-row gap-10 w-full"},F={class:"md:w-4/12 w-full"},M={class:"bg-white shadow rounded-lg p-6 mb-6"},j={class:"text-xl font-semibold mb-4"},z={class:"flex justify-end space-x-2"},H={type:"submit",class:"px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"},O={class:"md:w-8/12 w-full"},Q={class:"mb-4"},G={class:"bg-white shadow rounded-lg overflow-scroll"},R={class:"min-w-full divide-y divide-gray-200"},J={class:"bg-white divide-y divide-gray-200"},K={class:"py-1 px-2 whitespace bg-gray-50"},W={class:"py-1 px-2 whitespace-nowrap"},X={class:"py-1 px-2 whitespace bg-gray-50"},Y={class:"py-1 px-2 whitespace-nowrap"},Z={class:"py-1 px-2 whitespace-nowrap bg-gray-50"},ee={class:"py-1 px-2 whitespace-nowrap text-sm font-medium flex flex-col gap-1 place-items-center"},te=["onClick"],oe=["onClick"],re={__name:"Inventario",setup(v){const{props:y}=B(),l=f(y.inventario),r=I({id:null,nombre:"",detalle:"",tipo:"",cantidad:0,precio:0}),d=f(!1),u=f(""),w=E(()=>l.value.filter(s=>s.nombre.toLowerCase().includes(u.value.toLowerCase())||s.tipo.toLowerCase().includes(u.value.toLowerCase()))),n=p.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:1500,timerProgressBar:!0,didOpen:s=>{s.onmouseenter=p.stopTimer,s.onmouseleave=p.resumeTimer}}),h=()=>{d.value?x.put(`/inventario/${r.id}`,{nombre:r.nombre,detalle:r.detalle,tipo:r.tipo,cantidad:r.cantidad,precio:r.precio},{onSuccess:s=>{n.fire({icon:"success",title:"Actualizado correctamente"}),l.value=s.props.inventario,g()},onError:s=>{n.fire({icon:"error",title:"Hubo un problema al actualizar el ítem"})}}):x.post("/inventario",{nombre:r.nombre,detalle:r.detalle,tipo:r.tipo,cantidad:r.cantidad,precio:r.precio},{onSuccess:s=>{n.fire({icon:"success",title:"Agregado correctamente"}),l.value=s.props.inventario,g()},onError:s=>{n.fire({icon:"error",title:"Hubo un problema al agregar el ítem"})}})},_=s=>{Object.assign(r,s),d.value=!0},k=s=>{p.fire({title:"¿Estás seguro?",text:"¡No podrás revertir esto!",icon:"warning",showCancelButton:!0,confirmButtonColor:"#e66f23",cancelButtonColor:"#3085d6",confirmButtonText:"Sí, eliminarlo",cancelButtonText:"Cancelar"}).then(t=>{t.isConfirmed&&x.delete(`/inventario/${s}`,{onSuccess:o=>{n.fire({icon:"success",title:"Eliminado correctamente"}),l.value=o.props.inventario},onError:o=>{n.fire({icon:"error",title:"Hubo un problema al eliminar el ítem"})}})})},g=()=>{Object.assign(r,{id:null,nombre:"",detalle:"",tipo:"",cantidad:0,precio:0}),d.value=!1};return(s,t)=>(m(),b("div",q,[t[14]||(t[14]=e("h1",{class:"text-2xl font-bold mb-4"},"Gestión de Inventario",-1)),e("div",L,[e("div",F,[e("div",M,[e("h2",j,a(d.value?"Editar Item":"Agregar Nuevo Item"),1),e("form",{onSubmit:S(h,["prevent"]),class:"space-y-4"},[e("div",null,[t[7]||(t[7]=e("label",{for:"itemName",class:"block text-sm font-medium text-gray-700"},"Nombre del Item",-1)),i(e("input",{"onUpdate:modelValue":t[0]||(t[0]=o=>r.nombre=o),id:"itemName",type:"text",required:"",class:"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50"},null,512),[[c,r.nombre]])]),e("div",null,[t[9]||(t[9]=e("label",{for:"itemCategory",class:"block text-sm font-medium text-gray-700"},"Categoría",-1)),i(e("select",{"onUpdate:modelValue":t[1]||(t[1]=o=>r.tipo=o),id:"itemCategory",required:"",class:"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50"},t[8]||(t[8]=[N('<option value="relleno">Rellenos</option><option value="pastes">Pastes</option><option value="empanadas saladas">Empanadas saladas</option><option value="empanadas dulces">Empanadas dulces</option><option value="bebida">Bebidas</option><option value="masa">Masa</option><option value="extras">Extras</option>',7)]),512),[[$,r.tipo]])]),e("div",null,[t[10]||(t[10]=e("label",{for:"itemDetalle",class:"block text-sm font-medium text-gray-700"},"Detalle del item",-1)),i(e("input",{"onUpdate:modelValue":t[2]||(t[2]=o=>r.detalle=o),id:"itemDetalle",type:"text",class:"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50"},null,512),[[c,r.detalle]])]),e("div",null,[t[11]||(t[11]=e("label",{for:"itemQuantity",class:"block text-sm font-medium text-gray-700"},"Cantidad",-1)),i(e("input",{"onUpdate:modelValue":t[3]||(t[3]=o=>r.cantidad=o),id:"itemQuantity",type:"number",required:"",min:"0",class:"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50"},null,512),[[c,r.cantidad,void 0,{number:!0}]])]),e("div",null,[t[12]||(t[12]=e("label",{for:"itemPrice",class:"block text-sm font-medium text-gray-700"},"Precio Unitario",-1)),i(e("input",{"onUpdate:modelValue":t[4]||(t[4]=o=>r.precio=o),id:"itemPrice",type:"number",required:"",min:"0",step:"0.01",class:"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50"},null,512),[[c,r.precio,void 0,{number:!0}]])]),e("div",z,[e("button",{type:"button",onClick:g,class:"px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-gray-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"}," Cancelar "),e("button",H,a(d.value?"Actualizar":"Agregar"),1)])],32)])]),e("div",O,[e("div",Q,[i(e("input",{"onUpdate:modelValue":t[5]||(t[5]=o=>u.value=o),type:"text",placeholder:"Buscar en el inventario...",class:"w-full px-4 py-2 rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50",onInput:t[6]||(t[6]=(...o)=>s.searchInventory&&s.searchInventory(...o))},null,544),[[c,u.value]])]),e("div",G,[e("table",R,[t[13]||(t[13]=e("thead",{class:"bg-gray-50"},[e("tr",null,[e("th",{scope:"col",class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"}," Nombre"),e("th",{scope:"col",class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"}," Categoría"),e("th",{scope:"col",class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"}," Detalle"),e("th",{scope:"col",class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"}," Cantidad"),e("th",{scope:"col",class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"}," Precio"),e("th",{scope:"col",class:"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"}," Acciones")])],-1)),e("tbody",J,[(m(!0),b(V,null,T(w.value,o=>(m(),b("tr",{key:o.id,class:"text-center"},[e("td",K,a(o.nombre),1),e("td",W,a(o.tipo),1),e("td",X,a(o.detalle),1),e("td",Y,a(o.cantidad),1),e("td",Z,"$"+a(o.precio),1),e("td",ee,[e("button",{onClick:C=>_(o),class:"bg-orange-600 py-1 px-2 text-white rounded-lg hover:bg-orange-900 mr-2"},"Editar",8,te),e("button",{onClick:C=>k(o.id),class:"bg-red-600 py-1 px-2 text-white rounded-lg hover:bg-red-900"},"Eliminar",8,oe)])]))),128))])])])])])]))}},se={class:"py-6 px-2"},ae={class:"max-w-7xl mx-auto sm:px-6 lg:px-8"},ne={class:"bg-white overflow-hidden shadow-xl rounded-lg py-2"},ue={__name:"index",setup(v){return(y,l)=>(m(),U(P,{title:"Tablero principal"},{default:A(()=>[e("div",se,[e("div",ae,[e("div",ne,[D(re)])])])]),_:1}))}};export{ue as default};