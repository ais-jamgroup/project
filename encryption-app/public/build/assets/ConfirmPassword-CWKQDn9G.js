import{C as n,c as l,o as d,w as t,a as e,u as o,m as p,b as r,d as u,n as f,e as c}from"./app-t07WYJaO.js";import{_ as w}from"./GuestLayout-Sk38ivte.js";import{_,a as b,b as x}from"./TextInput-uoW7irsh.js";import{P as g}from"./PrimaryButton-BcqSVXfD.js";import"./ApplicationLogo-CPIJv2kw.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";const y={class:"mt-4 flex justify-end"},T={__name:"ConfirmPassword",setup(C){const s=n({password:""}),i=()=>{s.post(route("password.confirm"),{onFinish:()=>s.reset()})};return(P,a)=>(d(),l(w,null,{default:t(()=>[e(o(p),{title:"Confirm Password"}),a[2]||(a[2]=r("div",{class:"mb-4 text-sm text-gray-600 dark:text-gray-400"}," This is a secure area of the application. Please confirm your password before continuing. ",-1)),r("form",{onSubmit:c(i,["prevent"])},[r("div",null,[e(_,{for:"password",value:"Password"}),e(b,{id:"password",type:"password",class:"mt-1 block w-full",modelValue:o(s).password,"onUpdate:modelValue":a[0]||(a[0]=m=>o(s).password=m),required:"",autocomplete:"current-password",autofocus:""},null,8,["modelValue"]),e(x,{class:"mt-2",message:o(s).errors.password},null,8,["message"])]),r("div",y,[e(g,{class:f(["ms-4",{"opacity-25":o(s).processing}]),disabled:o(s).processing},{default:t(()=>a[1]||(a[1]=[u(" Confirm ")])),_:1},8,["class","disabled"])])],32)]),_:1}))}};export{T as default};
