import{C as c,h as g,c as p,w as o,o as a,a as i,u as t,m as y,b as r,f as k,g as x,d as n,n as v,P as b,e as h}from"./app-t07WYJaO.js";import{_}from"./GuestLayout-Sk38ivte.js";import{P as w}from"./PrimaryButton-BcqSVXfD.js";import"./ApplicationLogo-CPIJv2kw.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";const V={key:0,class:"mb-4 text-sm font-medium text-green-600 dark:text-green-400"},B={class:"mt-4 flex items-center justify-between"},j={__name:"VerifyEmail",props:{status:{type:String}},setup(d){const u=d,s=c({}),l=()=>{s.post(route("verification.send"))},m=g(()=>u.status==="verification-link-sent");return(f,e)=>(a(),p(_,null,{default:o(()=>[i(t(y),{title:"Email Verification"}),e[2]||(e[2]=r("div",{class:"mb-4 text-sm text-gray-600 dark:text-gray-400"}," Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another. ",-1)),m.value?(a(),k("div",V," A new verification link has been sent to the email address you provided during registration. ")):x("",!0),r("form",{onSubmit:h(l,["prevent"])},[r("div",B,[i(w,{class:v({"opacity-25":t(s).processing}),disabled:t(s).processing},{default:o(()=>e[0]||(e[0]=[n(" Resend Verification Email ")])),_:1},8,["class","disabled"]),i(t(b),{href:f.route("logout"),method:"post",as:"button",class:"rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"},{default:o(()=>e[1]||(e[1]=[n("Log Out")])),_:1},8,["href"])])],32)]),_:1}))}};export{j as default};
