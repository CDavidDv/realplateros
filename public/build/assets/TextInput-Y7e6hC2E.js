import{l,G as c,o,e as r,a as i,t as d,d as p,p as m}from"./app-CjNhnG7k.js";const _={class:"disabled"},f={class:"text-xs text-red-600"},h={__name:"InputError",props:{message:String},setup(s){return(t,e)=>l((o(),r("div",_,[i("p",f,d(s.message),1)],512)),[[c,s.message]])}},g=["value"],x={__name:"TextInput",props:{modelValue:String},emits:["update:modelValue"],setup(s,{expose:t}){const e=p(null);return m(()=>{e.value.hasAttribute("autofocus")&&e.value.focus()}),t({focus:()=>e.value.focus()}),(u,a)=>(o(),r("input",{ref_key:"input",ref:e,class:"border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm",value:s.modelValue,onInput:a[0]||(a[0]=n=>u.$emit("update:modelValue",n.target.value))},null,40,g))}};export{x as _,h as a};
