import{d as g,T as k,c as x,w as o,o as a,g as n,a as t,e as i,F as h,h as V,f as b,b as l,u as d,D as C,n as S,t as u}from"./app-E2UCKAIA.js";import{_ as B}from"./ActionMessage-DIZXiCCq.js";import{a as A,b as L}from"./DialogModal-BuU0ckh-.js";import{_ as $,a as H}from"./TextInput-CXNN2gSJ.js";import{_ as v}from"./PrimaryButton-yMNb18b9.js";import{_ as O}from"./SecondaryButton-fvZ66MzR.js";import"./SectionTitle-Bg21Az7k.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";const F={key:0,class:"mt-5 space-y-6"},N={key:0,class:"w-8 h-8 text-gray-500",xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor"},E={key:1,class:"w-8 h-8 text-gray-500",xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor"},M={class:"ms-3"},T={class:"text-sm text-gray-600"},U={class:"text-xs text-gray-500"},j={key:0,class:"text-green-500 font-semibold"},D={key:1},I={class:"flex items-center mt-5"},K={class:"mt-4"},Y={__name:"LogoutOtherBrowserSessionsForm",props:{sessions:Array},setup(f){const m=g(!1),c=g(null),r=k({password:""}),_=()=>{m.value=!0,setTimeout(()=>c.value.focus(),250)},w=()=>{r.delete(route("other-browser-sessions.destroy"),{preserveScroll:!0,onSuccess:()=>p(),onError:()=>c.value.focus(),onFinish:()=>r.reset()})},p=()=>{m.value=!1,r.reset()};return(P,s)=>(a(),x(A,{class:"bg-white p-8 rounded-xl"},{title:o(()=>s[1]||(s[1]=[n(" Sesiones en navegadores ")])),description:o(()=>s[2]||(s[2]=[n(" Gestione y cierre sus sesiones activas en otros navegadores y dispositivos. ")])),content:o(()=>[s[11]||(s[11]=t("div",{class:"max-w-xl text-sm text-gray-600"}," Si es necesario, puede cerrar todas sus sesiones de navegación en todos sus dispositivos. A continuación se enumeran algunas de sus sesiones recientes; sin embargo, esta lista puede no ser exhaustiva. Si cree que su cuenta se ha visto comprometida, actualice también su contraseña. ",-1)),f.sessions.length>0?(a(),i("div",F,[(a(!0),i(h,null,V(f.sessions,(e,y)=>(a(),i("div",{key:y,class:"flex items-center"},[t("div",null,[e.agent.is_desktop?(a(),i("svg",N,s[3]||(s[3]=[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"},null,-1)]))):(a(),i("svg",E,s[4]||(s[4]=[t("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"},null,-1)])))]),t("div",M,[t("div",T,u(e.agent.platform?e.agent.platform:"Unknown")+" - "+u(e.agent.browser?e.agent.browser:"Unknown"),1),t("div",null,[t("div",U,[n(u(e.ip_address)+", ",1),e.is_current_device?(a(),i("span",j,"Este dispositivo")):(a(),i("span",D,"Last active "+u(e.last_active),1))])])])]))),128))])):b("",!0),t("div",I,[l(v,{onClick:_},{default:o(()=>s[5]||(s[5]=[n(" Cerrar otras sesiones del navegador ")])),_:1}),l(B,{on:d(r).recentlySuccessful,class:"ms-3"},{default:o(()=>s[6]||(s[6]=[n(" Hecho. ")])),_:1},8,["on"])]),l(L,{show:m.value,onClose:p},{title:o(()=>s[7]||(s[7]=[n(" Log Out Other Browser Sessions ")])),content:o(()=>[s[8]||(s[8]=n(" Please enter your password to confirm you would like to log out of your other browser sessions across all of your devices. ")),t("div",K,[l($,{ref_key:"passwordInput",ref:c,modelValue:d(r).password,"onUpdate:modelValue":s[0]||(s[0]=e=>d(r).password=e),type:"password",class:"mt-1 block w-3/4",placeholder:"Password",autocomplete:"current-password",onKeyup:C(w,["enter"])},null,8,["modelValue"]),l(H,{message:d(r).errors.password,class:"mt-2"},null,8,["message"])])]),footer:o(()=>[l(O,{onClick:p},{default:o(()=>s[9]||(s[9]=[n(" Cancel ")])),_:1}),l(v,{class:S(["ms-3",{"opacity-25":d(r).processing}]),disabled:d(r).processing,onClick:w},{default:o(()=>s[10]||(s[10]=[n(" Log Out Other Browser Sessions ")])),_:1},8,["class","disabled"])]),_:1},8,["show"])]),_:1}))}};export{Y as default};