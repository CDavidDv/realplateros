import{T as m,d,e as u,b as o,u as e,w as t,F as p,o as f,Z as c,a as r,n as _,g as w,i as g}from"./app-CjNhnG7k.js";import{A as b}from"./AuthenticationCard-B8lUHSn3.js";import{_ as x}from"./AuthenticationCardLogo-jtmE5f5E.js";import{_ as v,a as y}from"./TextInput-Y7e6hC2E.js";import{_ as V}from"./InputLabel-C3CcKqPq.js";import{_ as C}from"./PrimaryButton-DPPAgsUu.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";const $={class:"flex justify-end mt-4"},S={__name:"ConfirmPassword",setup(k){const s=m({password:""}),i=d(null),n=()=>{s.post(route("password.confirm"),{onFinish:()=>{s.reset(),i.value.focus()}})};return(A,a)=>(f(),u(p,null,[o(e(c),{title:"Secure Area"}),o(b,null,{logo:t(()=>[o(x)]),default:t(()=>[a[2]||(a[2]=r("div",{class:"mb-4 text-sm text-gray-600"}," This is a secure area of the application. Please confirm your password before continuing. ",-1)),r("form",{onSubmit:g(n,["prevent"])},[r("div",null,[o(V,{for:"password",value:"Password"}),o(v,{id:"password",ref_key:"passwordInput",ref:i,modelValue:e(s).password,"onUpdate:modelValue":a[0]||(a[0]=l=>e(s).password=l),type:"password",class:"mt-1 block w-full",required:"",autocomplete:"current-password",autofocus:""},null,8,["modelValue"]),o(y,{class:"mt-2",message:e(s).errors.password},null,8,["message"])]),r("div",$,[o(C,{class:_(["ms-4",{"opacity-25":e(s).processing}]),disabled:e(s).processing},{default:t(()=>a[1]||(a[1]=[w(" Confirm ")])),_:1},8,["class","disabled"])])],32)]),_:1})],64))}};export{S as default};
