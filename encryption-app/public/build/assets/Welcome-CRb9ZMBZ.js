import{r as u,f as p,a,u as t,b as o,c as l,w as i,g as c,F as f,o as s,m as w,d as m,P as d}from"./app-DOWDtDR1.js";const y={class:"welcome-container"},b={class:"welcome-logo"},h={class:"welcome-buttons"},v={__name:"Welcome",props:{canLogin:{type:Boolean},canRegister:{type:Boolean}},setup(n){return(r,e)=>{const g=u("lord-icon");return s(),p(f,null,[a(t(w),{title:"Welcome"}),o("div",y,[o("div",b,[a(g,{src:"https://cdn.lordicon.com/jdgfsfzr.json",trigger:"loop",colors:"primary:#121331,secondary:#e8e230",style:{width:"250px",height:"250px"}})]),e[2]||(e[2]=o("h1",{class:"welcome-title"},"DeepText",-1)),e[3]||(e[3]=o("p",{class:"welcome-subtitle"}," Experience messaging with peace of mind. DeepText protects your privacy, ",-1)),e[4]||(e[4]=o("p",{class:"welcome-subtitle"}," so you can focus on what matters—staying connected, sharing moments, and building meaningful conversations. ",-1)),o("div",h,[n.canLogin?(s(),l(t(d),{key:0,href:r.route("login"),class:"welcome-login-button"},{default:i(()=>e[0]||(e[0]=[m(" Log in ")])),_:1},8,["href"])):c("",!0),n.canRegister?(s(),l(t(d),{key:1,href:r.route("register"),class:"welcome-register-button"},{default:i(()=>e[1]||(e[1]=[m(" Register ")])),_:1},8,["href"])):c("",!0)])])],64)}}};export{v as default};
