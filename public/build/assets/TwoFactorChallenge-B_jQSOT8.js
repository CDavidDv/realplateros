import{d,T as x,e as s,b as o,u as t,w as m,F as n,o as a,Z as C,a as i,g as l,j as p,n as w,p as V}from"./app-CIHGODiB.js";import{A as h}from"./AuthenticationCard-5-9wvo4M.js";import{_ as T}from"./AuthenticationCardLogo-COyHPPBq.js";import{_,a as v}from"./TextInput-DyacFwRi.js";import{_ as g}from"./InputLabel-EyYD7O05.js";import{_ as $}from"./PrimaryButton-BsXi8LyA.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";const I={class:"mb-4 text-sm text-gray-600"},U={key:0},B={key:1},F={class:"flex items-center justify-end mt-4"},S={__name:"TwoFactorChallenge",setup(N){const c=d(!1),e=x({code:"",recovery_code:""}),f=d(null),y=d(null),k=async()=>{c.value^=!0,await V(),c.value?(f.value.focus(),e.code=""):(y.value.focus(),e.recovery_code="")},b=()=>{e.post(route("two-factor.login"))};return(j,r)=>(a(),s(n,null,[o(t(C),{title:"Two-factor Confirmation"}),o(h,null,{logo:m(()=>[o(T)]),default:m(()=>[i("div",I,[c.value?(a(),s(n,{key:1},[l(" Please confirm access to your account by entering one of your emergency recovery codes. ")],64)):(a(),s(n,{key:0},[l(" Please confirm access to your account by entering the authentication code provided by your authenticator application. ")],64))]),i("form",{onSubmit:p(b,["prevent"])},[c.value?(a(),s("div",B,[o(g,{for:"recovery_code",value:"Recovery Code"}),o(_,{id:"recovery_code",ref_key:"recoveryCodeInput",ref:f,modelValue:t(e).recovery_code,"onUpdate:modelValue":r[1]||(r[1]=u=>t(e).recovery_code=u),type:"text",class:"mt-1 block w-full",autocomplete:"one-time-code"},null,8,["modelValue"]),o(v,{class:"mt-2",message:t(e).errors.recovery_code},null,8,["message"])])):(a(),s("div",U,[o(g,{for:"code",value:"Code"}),o(_,{id:"code",ref_key:"codeInput",ref:y,modelValue:t(e).code,"onUpdate:modelValue":r[0]||(r[0]=u=>t(e).code=u),type:"text",inputmode:"numeric",class:"mt-1 block w-full",autofocus:"",autocomplete:"one-time-code"},null,8,["modelValue"]),o(v,{class:"mt-2",message:t(e).errors.code},null,8,["message"])])),i("div",F,[i("button",{type:"button",class:"text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer",onClick:p(k,["prevent"])},[c.value?(a(),s(n,{key:1},[l(" Use an authentication code ")],64)):(a(),s(n,{key:0},[l(" Use a recovery code ")],64))]),o($,{class:w(["ms-4",{"opacity-25":t(e).processing}]),disabled:t(e).processing},{default:m(()=>r[2]||(r[2]=[l(" Log in ")])),_:1},8,["class","disabled"])])],32)]),_:1})],64))}};export{S as default};
