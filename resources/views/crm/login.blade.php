<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>FaresHunt CRM Login</title>
<link rel="icon" type="image/x-icon" href="/assets/img/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="/assets/css/fontawesome.css" rel="stylesheet">
<style>
*{box-sizing:border-box}html,body{margin:0;min-height:100%;font-family:Inter,Arial,sans-serif;color:#08275c}body{min-height:100vh;background:radial-gradient(circle at 12% 8%,rgba(86,151,238,.13),transparent 28%),radial-gradient(circle at 91% 88%,rgba(64,120,199,.08),transparent 28%),linear-gradient(135deg,#f6faff 0%,#edf5ff 50%,#f8fbff 100%)}button,input{font:inherit}.fhcrm-page{min-height:100vh;padding:20px;display:flex;align-items:center;justify-content:center}.fhcrm-shell{position:relative;width:min(1560px,98vw);min-height:calc(100vh - 40px);display:grid;grid-template-columns:minmax(0,1.42fr) minmax(520px,.96fr);border:1px solid #dbe6f3;border-radius:26px;overflow:hidden;background:linear-gradient(90deg,rgba(255,255,255,.99),rgba(247,251,255,.98));box-shadow:0 28px 85px rgba(17,53,102,.13)}
.fhcrm-left{position:relative;min-height:900px;padding:34px 34px 28px;overflow:hidden}.fhcrm-logo{width:300px;max-width:55%;height:auto;display:block;position:relative;z-index:10}.fhcrm-copy{position:relative;z-index:10;margin-top:34px}.fhcrm-copy h1{margin:0;font-size:42px;line-height:1.02;letter-spacing:-.045em;font-weight:800;color:#082a61}.fhcrm-copy h1 span{display:block;color:#db1747}.fhcrm-copy p{margin:14px 0 0;font-size:16px;font-weight:500;color:#274d7d}.fhcrm-copy:after{content:"";display:block;width:32px;height:3px;margin-top:19px;border-radius:99px;background:#db1747}.fhcrm-plane{position:absolute;z-index:7;top:82px;right:68px;color:#bacde3;font-size:68px;transform:rotate(12deg);filter:drop-shadow(0 12px 16px rgba(46,88,138,.16))}.fhcrm-map{position:absolute;left:22px;right:18px;top:220px;height:445px;z-index:2}.fhcrm-map svg{width:100%;height:100%}.fhcrm-pin{position:absolute;z-index:8;transform:translate(-50%,-50%);text-align:center}.fhcrm-pin i{display:block;font-size:22px;color:#dd1647;filter:drop-shadow(0 2px 4px rgba(9,43,87,.18))}.fhcrm-pin.blue i{color:#0d438f}.fhcrm-pin span{display:inline-block;margin-top:3px;padding:4px 7px;background:#fff;border-radius:4px;box-shadow:0 4px 12px rgba(24,64,113,.12);font-size:8px;font-weight:600;color:#163d70;white-space:nowrap}.p1{left:15%;top:23%}.p2{left:19%;top:46%}.p3{left:31%;top:76%}.p4{left:49%;top:23%}.p5{left:52%;top:39%}.p6{left:54%;top:59%}.p7{left:65%;top:57%}.p8{left:75%;top:52%}.p9{left:80%;top:32%}.p10{left:92%;top:42%}.p11{left:84%;top:67%}.p12{left:90%;top:82%}.fhcrm-features{position:absolute;left:34px;right:34px;bottom:172px;z-index:10;display:grid;grid-template-columns:repeat(4,1fr);gap:12px}.fhcrm-feature{min-height:70px;display:flex;align-items:center;gap:11px;padding:12px;border:1px solid #d7e4f4;border-radius:10px;background:rgba(255,255,255,.9);box-shadow:0 8px 24px rgba(26,63,108,.05)}.fhcrm-feature-icon{width:44px;height:44px;flex:0 0 44px;display:grid;place-items:center;border:1px solid #a8c8ed;border-radius:50%;background:#fbfdff;color:#0d4a99;font-size:20px}.fhcrm-feature b{display:block;margin-bottom:3px;font-size:10px;color:#0e3164}.fhcrm-feature small{display:block;font-size:8px;line-height:1.4;color:#4c6888}.fhcrm-countries{position:absolute;z-index:10;left:34px;right:34px;bottom:70px;padding:13px 16px;border:1px solid #d9e5f2;border-radius:12px;background:rgba(255,255,255,.94)}.fhcrm-countries-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;font-size:10px;font-weight:700;color:#123665}.fhcrm-countries-head a{color:#0868dc;text-decoration:none;font-weight:500}.fhcrm-country-row{display:grid;grid-template-columns:repeat(9,1fr);gap:10px}.fhcrm-country{text-align:center;font-size:7px;font-weight:500;color:#173b69}.fhcrm-flag{width:32px;height:32px;margin:0 auto 6px;display:grid;place-items:center;border-radius:50%;background:#fff;box-shadow:0 3px 9px rgba(29,63,106,.12);font-size:21px}.fhcrm-skyline{position:absolute;left:24px;right:24px;bottom:5px;height:68px;display:flex;align-items:flex-end;justify-content:space-around;color:#4b8bd2;opacity:.26;font-size:40px}.fhcrm-copyline{position:absolute;z-index:20;left:30%;bottom:10px;font-size:9px;color:#3b5d85}
.fhcrm-right{position:relative;z-index:12;padding:36px 34px 36px 8px;display:flex;align-items:center;justify-content:center}.fhcrm-ticket{position:relative;width:100%;min-height:850px;overflow:hidden;border:1px solid #c9daed;border-radius:28px 28px 22px 22px;background:#fff;box-shadow:0 28px 58px rgba(14,50,99,.19)}.fhcrm-ticket:before{content:"";position:absolute;z-index:5;top:-18px;left:50%;width:50px;height:38px;transform:translateX(-50%);border-radius:0 0 30px 30px;background:#f5f9ff}.fhcrm-ticket-head{position:relative;height:132px;padding:28px 29px;display:flex;justify-content:space-between;color:#fff;background:radial-gradient(circle at 49% 40%,rgba(59,110,185,.16),transparent 24%),linear-gradient(105deg,#061e52,#012a70 72%,#061d4a)}.fhcrm-ticket-head:after{content:"";position:absolute;left:0;right:0;bottom:-4px;height:8px;background:radial-gradient(circle,#fff 2px,transparent 2.5px) 0 0/8px 8px repeat-x}.fhcrm-ticket-title{display:flex;gap:17px;align-items:flex-start}.fhcrm-ticket-plane{width:50px;height:50px;display:grid;place-items:center;border:1px solid rgba(111,170,243,.48);border-radius:8px;background:rgba(17,69,145,.43);font-size:25px}.fhcrm-ticket-title h2{margin:7px 0 0;font-size:26px;line-height:1;font-weight:800}.fhcrm-ticket-title p{margin:12px 0 0;font-size:11px;font-weight:600}.fhcrm-code{padding-left:24px;border-left:1px dashed rgba(255,255,255,.65);text-align:center}.fhcrm-code strong{display:block;font-size:38px;line-height:1}.fhcrm-code span{display:block;margin-top:8px;font-size:10px}.fhcrm-ticket-main{display:grid;grid-template-columns:minmax(0,1fr) 104px;min-height:500px}.fhcrm-login{padding:50px 36px 35px}.fhcrm-login h3{margin:0;font-size:34px;line-height:1;font-weight:800;color:#08275c}.fhcrm-login>p{margin:15px 0 0;color:#496990;font-size:13px}.fhcrm-errors{margin:22px 0 -12px;padding:11px 13px;border:1px solid #ffc5d2;border-radius:8px;background:#fff1f4;color:#b8173b;font-size:12px}.fhcrm-form{margin-top:38px}.fhcrm-field{position:relative;margin-bottom:18px}.fhcrm-field>i{position:absolute;left:18px;top:50%;transform:translateY(-50%);z-index:2;width:22px;text-align:center;color:#124a96;font-size:19px}.fhcrm-input{width:100%;height:62px;padding:0 52px 0 58px;border:1px solid #bed3eb;border-radius:8px;outline:0;background:#fff;color:#0d3266;font-size:14px;transition:.2s}.fhcrm-input::placeholder{color:#6781a4}.fhcrm-input:focus{border-color:#4d82c0;box-shadow:0 0 0 4px rgba(78,131,194,.08)}.fhcrm-eye{position:absolute;right:18px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:#47719e;font-size:17px;cursor:pointer}.fhcrm-options{margin:23px 0 34px;display:flex;align-items:center;justify-content:space-between;gap:18px;color:#3d5e84;font-size:12px}.fhcrm-remember{display:flex;align-items:center;gap:9px}.fhcrm-remember input{width:19px;height:19px;accent-color:#d91848}.fhcrm-forgot{color:#0868dc;text-decoration:none;font-weight:500}.fhcrm-submit{position:relative;width:100%;height:60px;border:0;border-radius:7px;background:linear-gradient(100deg,#dc1647,#dc1544 50%,#e31d50);color:#fff;font-size:16px;font-weight:800;cursor:pointer;box-shadow:0 14px 25px rgba(220,22,69,.18);transition:.2s}.fhcrm-submit:hover{transform:translateY(-1px);box-shadow:0 18px 30px rgba(220,22,69,.24)}.fhcrm-submit i{position:absolute;left:20px;top:50%;transform:translateY(-50%);font-size:20px}.fhcrm-rail{position:relative;border-left:1px dashed #b8cce4;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#092d63}.fhcrm-barcode{width:37px;height:180px;margin-bottom:34px;background:repeating-linear-gradient(90deg,#082a61 0 2px,transparent 2px 4px,#082a61 4px 5px,transparent 5px 8px)}.fhcrm-railtext{writing-mode:vertical-rl;transform:rotate(180deg);letter-spacing:.11em;font-size:18px;font-weight:500}.fhcrm-railtext small{font-size:9px;letter-spacing:.2em;margin-top:9px}.fhcrm-info{border-top:1px dashed #a8bfdc}.fhcrm-info-row{min-height:84px;display:grid;grid-template-columns:repeat(4,1fr);align-items:center;padding:0 27px;border-bottom:1px solid #d9e4f0}.fhcrm-info-row.second{min-height:72px;grid-template-columns:repeat(3,1fr)}.fhcrm-info-item{padding:0 14px;border-right:1px solid #dbe5f1}.fhcrm-info-item:last-child{border-right:0}.fhcrm-info-item small{display:block;margin-bottom:6px;color:#7185a2;font-size:8px;font-weight:600}.fhcrm-info-item b{display:block;color:#092b60;font-size:14px}.fhcrm-info-item b.red{color:#d91848}.fhcrm-ticket-foot{min-height:118px;display:flex;align-items:center;justify-content:center;gap:20px;padding:18px 28px;background:linear-gradient(180deg,#fbfdff,#eef5fd)}.fhcrm-ticket-foot i{font-size:34px;color:#0a3c84;transform:rotate(-18deg)}.fhcrm-ticket-foot p{margin:0;font-size:13px;line-height:1.55;color:#173e72}.fhcrm-ticket-foot em{display:block;color:#d91949;font-weight:700}
@media(max-width:1120px){.fhcrm-shell{grid-template-columns:1fr 520px}.fhcrm-features,.fhcrm-countries{left:24px;right:24px}}@media(max-width:980px){.fhcrm-page{padding:0}.fhcrm-shell{width:100%;min-height:100vh;grid-template-columns:1fr;border-radius:0}.fhcrm-left{display:none}.fhcrm-right{padding:24px;min-height:100vh}.fhcrm-ticket{max-width:620px}.fhcrm-copyline{display:none}}@media(max-width:600px){.fhcrm-right{padding:10px}.fhcrm-ticket-main{grid-template-columns:1fr}.fhcrm-rail{display:none}.fhcrm-login{padding:36px 22px 28px}.fhcrm-ticket-head{padding:25px 18px}.fhcrm-ticket-title h2{font-size:21px}.fhcrm-code{padding-left:12px}.fhcrm-info-row{grid-template-columns:repeat(2,1fr);gap:18px 0;padding:18px 9px}.fhcrm-info-row.second{grid-template-columns:1fr;gap:14px;padding:18px}}
</style>

<style id="fhcrm-compact-desktop-v2">
/* =========================================================
   Compact desktop composition — target: fit in one viewport
   Keeps existing authentication/backend untouched.
   ========================================================= */
@media (min-width:981px){
    html,body{
        height:100%;
        overflow:hidden;
    }

    .fhcrm-page{
        min-height:100vh;
        height:100vh;
        padding:10px 14px;
        overflow:hidden;
    }

    .fhcrm-shell{
        width:min(1760px,98.5vw);
        height:calc(100vh - 20px);
        min-height:0;
        grid-template-columns:minmax(0,1.45fr) minmax(520px,.95fr);
        border-radius:24px;
    }

    /* LEFT */
    .fhcrm-left{
        min-height:0;
        height:100%;
        padding:24px 28px 18px;
    }

    .fhcrm-logo{
        width:250px;
        max-width:43%;
    }

    .fhcrm-copy{
        margin-top:18px;
    }

    .fhcrm-copy h1{
        font-size:35px;
        line-height:1.01;
    }

    .fhcrm-copy p{
        margin-top:10px;
        font-size:14px;
    }

    .fhcrm-copy:after{
        margin-top:14px;
    }

    .fhcrm-plane{
        top:60px;
        right:58px;
        font-size:54px;
    }

    .fhcrm-map{
        top:178px;
        left:18px;
        right:12px;
        height:330px;
    }

    .fhcrm-pin i{
        font-size:18px;
    }

    .fhcrm-pin span{
        font-size:7px;
        padding:3px 6px;
    }

    .fhcrm-features{
        left:28px;
        right:28px;
        bottom:142px;
        gap:10px;
    }

    .fhcrm-feature{
        min-height:60px;
        padding:9px;
        gap:9px;
    }

    .fhcrm-feature-icon{
        width:38px;
        height:38px;
        flex-basis:38px;
        font-size:17px;
    }

    .fhcrm-feature b{
        font-size:9px;
    }

    .fhcrm-feature small{
        font-size:7px;
        line-height:1.3;
    }

    .fhcrm-countries{
        left:28px;
        right:28px;
        bottom:56px;
        padding:10px 13px 9px;
    }

    .fhcrm-countries-head{
        margin-bottom:8px;
        font-size:9px;
    }

    .fhcrm-country-row{
        gap:8px;
    }

    .fhcrm-country{
        font-size:6.5px;
    }

    .fhcrm-flag{
        width:28px;
        height:28px;
        margin-bottom:4px;
        font-size:18px;
    }

    .fhcrm-skyline{
        height:44px;
        bottom:2px;
        font-size:30px;
    }

    .fhcrm-copyline{
        bottom:5px;
        font-size:8px;
    }

    /* RIGHT */
    .fhcrm-right{
        height:100%;
        padding:18px 26px 18px 8px;
    }

    .fhcrm-ticket{
        min-height:0;
        height:100%;
        border-radius:26px 26px 20px 20px;
        display:grid;
        grid-template-rows:112px minmax(0,1fr) auto 88px;
    }

    .fhcrm-ticket-head{
        height:auto;
        padding:22px 26px;
    }

    .fhcrm-ticket-plane{
        width:44px;
        height:44px;
        font-size:22px;
    }

    .fhcrm-ticket-title h2{
        margin-top:5px;
        font-size:22px;
    }

    .fhcrm-ticket-title p{
        margin-top:8px;
        font-size:10px;
    }

    .fhcrm-code strong{
        font-size:32px;
    }

    .fhcrm-code span{
        margin-top:6px;
        font-size:9px;
    }

    .fhcrm-ticket-main{
        min-height:0;
        grid-template-columns:minmax(0,1fr) 88px;
    }

    .fhcrm-login{
        padding:30px 30px 22px;
        align-self:start;
    }

    .fhcrm-login h3{
        font-size:29px;
    }

    .fhcrm-login>p{
        margin-top:10px;
        font-size:12px;
    }

    .fhcrm-form{
        margin-top:26px;
    }

    .fhcrm-field{
        margin-bottom:14px;
    }

    .fhcrm-input{
        height:52px;
        padding-left:52px;
        font-size:13px;
    }

    .fhcrm-field>i{
        left:17px;
        font-size:17px;
    }

    .fhcrm-options{
        margin:17px 0 24px;
        font-size:11px;
    }

    .fhcrm-remember input{
        width:17px;
        height:17px;
    }

    .fhcrm-submit{
        height:52px;
        font-size:14px;
    }

    .fhcrm-barcode{
        width:32px;
        height:140px;
        margin-bottom:24px;
    }

    .fhcrm-railtext{
        font-size:15px;
    }

    .fhcrm-railtext small{
        font-size:8px;
    }

    .fhcrm-info-row{
        min-height:64px;
        padding:0 20px;
    }

    .fhcrm-info-row.second{
        min-height:58px;
    }

    .fhcrm-info-item{
        padding:0 10px;
    }

    .fhcrm-info-item small{
        margin-bottom:4px;
        font-size:7px;
    }

    .fhcrm-info-item b{
        font-size:12px;
    }

    .fhcrm-ticket-foot{
        min-height:0;
        padding:12px 22px;
        gap:14px;
    }

    .fhcrm-ticket-foot i{
        font-size:28px;
    }

    .fhcrm-ticket-foot p{
        font-size:11px;
    }
}

/* Short laptop screens */
@media (min-width:981px) and (max-height:820px){
    .fhcrm-logo{width:220px}
    .fhcrm-copy h1{font-size:31px}
    .fhcrm-copy p{font-size:13px}
    .fhcrm-map{top:158px;height:280px}
    .fhcrm-features{bottom:130px}
    .fhcrm-countries{bottom:48px}
    .fhcrm-ticket{grid-template-rows:102px minmax(0,1fr) auto 76px}
    .fhcrm-login{padding:24px 26px 18px}
    .fhcrm-form{margin-top:22px}
    .fhcrm-input{height:48px}
    .fhcrm-submit{height:48px}
    .fhcrm-info-row{min-height:58px}
    .fhcrm-info-row.second{min-height:52px}
}
</style>


<style id="fhcrm-fit-screen-v3">
/* FINAL desktop fit pass based on the current rendered screenshot */
@media (min-width:981px){
    .fhcrm-page{
        padding:6px 10px!important;
    }

    .fhcrm-shell{
        width:calc(100vw - 20px)!important;
        height:calc(100vh - 12px)!important;
        min-height:0!important;
        max-height:calc(100vh - 12px)!important;
        border-radius:22px!important;
    }

    /* left composition */
    .fhcrm-left{
        height:100%!important;
        min-height:0!important;
        padding:20px 26px 14px!important;
    }

    .fhcrm-logo{
        width:215px!important;
        max-width:40%!important;
    }

    .fhcrm-copy{
        margin-top:14px!important;
    }

    .fhcrm-copy h1{
        font-size:30px!important;
        line-height:1!important;
    }

    .fhcrm-copy p{
        margin-top:8px!important;
        font-size:12px!important;
    }

    .fhcrm-copy:after{
        margin-top:11px!important;
        width:28px!important;
    }

    .fhcrm-plane{
        top:54px!important;
        right:52px!important;
        font-size:48px!important;
    }

    .fhcrm-map{
        top:154px!important;
        left:24px!important;
        right:18px!important;
        height:300px!important;
    }

    .fhcrm-pin i{
        font-size:16px!important;
    }

    .fhcrm-pin span{
        margin-top:2px!important;
        padding:2px 5px!important;
        font-size:6.5px!important;
    }

    .fhcrm-features{
        left:26px!important;
        right:26px!important;
        bottom:112px!important;
        gap:8px!important;
    }

    .fhcrm-feature{
        min-height:54px!important;
        padding:8px!important;
        gap:8px!important;
    }

    .fhcrm-feature-icon{
        width:34px!important;
        height:34px!important;
        flex:0 0 34px!important;
        font-size:15px!important;
    }

    .fhcrm-feature b{
        font-size:8px!important;
        margin-bottom:2px!important;
    }

    .fhcrm-feature small{
        font-size:6.5px!important;
        line-height:1.25!important;
    }

    .fhcrm-countries{
        left:26px!important;
        right:26px!important;
        bottom:38px!important;
        padding:7px 12px 7px!important;
        border-radius:9px!important;
    }

    .fhcrm-countries-head{
        margin-bottom:5px!important;
        font-size:8px!important;
    }

    .fhcrm-country-row{
        gap:5px!important;
    }

    .fhcrm-country{
        font-size:5.8px!important;
    }

    .fhcrm-flag{
        width:23px!important;
        height:23px!important;
        margin-bottom:2px!important;
        font-size:15px!important;
    }

    .fhcrm-skyline{
        bottom:0!important;
        height:30px!important;
        font-size:23px!important;
        opacity:.20!important;
    }

    .fhcrm-copyline{
        bottom:3px!important;
        font-size:7px!important;
    }

    /* boarding pass */
    .fhcrm-right{
        height:100%!important;
        min-height:0!important;
        padding:14px 24px 14px 6px!important;
    }

    .fhcrm-ticket{
        height:100%!important;
        min-height:0!important;
        max-height:100%!important;
        grid-template-rows:96px minmax(0,1fr) 100px 64px!important;
        border-radius:24px 24px 18px 18px!important;
    }

    .fhcrm-ticket-head{
        height:96px!important;
        padding:17px 24px!important;
    }

    .fhcrm-ticket-plane{
        width:40px!important;
        height:40px!important;
        font-size:19px!important;
    }

    .fhcrm-ticket-title{
        gap:13px!important;
    }

    .fhcrm-ticket-title h2{
        margin-top:4px!important;
        font-size:20px!important;
    }

    .fhcrm-ticket-title p{
        margin-top:7px!important;
        font-size:8px!important;
    }

    .fhcrm-code{
        padding-left:18px!important;
    }

    .fhcrm-code strong{
        font-size:28px!important;
    }

    .fhcrm-code span{
        margin-top:4px!important;
        font-size:8px!important;
    }

    .fhcrm-ticket-main{
        min-height:0!important;
        height:auto!important;
        grid-template-columns:minmax(0,1fr) 80px!important;
        overflow:hidden!important;
    }

    .fhcrm-login{
        padding:22px 28px 16px!important;
    }

    .fhcrm-login h3{
        font-size:25px!important;
    }

    .fhcrm-login>p{
        margin-top:8px!important;
        font-size:10.5px!important;
    }

    .fhcrm-form{
        margin-top:20px!important;
    }

    .fhcrm-field{
        margin-bottom:11px!important;
    }

    .fhcrm-input{
        height:46px!important;
        padding-left:48px!important;
        font-size:12px!important;
    }

    .fhcrm-field>i{
        left:16px!important;
        font-size:16px!important;
    }

    .fhcrm-eye{
        right:15px!important;
        font-size:15px!important;
    }

    .fhcrm-options{
        margin:13px 0 18px!important;
        font-size:10px!important;
    }

    .fhcrm-remember input{
        width:15px!important;
        height:15px!important;
    }

    .fhcrm-submit{
        height:46px!important;
        font-size:13px!important;
    }

    .fhcrm-submit i{
        left:18px!important;
        font-size:17px!important;
    }

    .fhcrm-barcode{
        width:28px!important;
        height:116px!important;
        margin-bottom:16px!important;
    }

    .fhcrm-railtext{
        font-size:13px!important;
    }

    .fhcrm-railtext small{
        font-size:7px!important;
    }

    .fhcrm-info{
        height:100px!important;
        overflow:hidden!important;
    }

    .fhcrm-info-row{
        min-height:0!important;
        height:52px!important;
        padding:0 16px!important;
    }

    .fhcrm-info-row.second{
        min-height:0!important;
        height:48px!important;
    }

    .fhcrm-info-item{
        padding:0 8px!important;
    }

    .fhcrm-info-item small{
        margin-bottom:3px!important;
        font-size:6px!important;
    }

    .fhcrm-info-item b{
        font-size:10.5px!important;
    }

    .fhcrm-ticket-foot{
        min-height:0!important;
        height:64px!important;
        padding:8px 18px!important;
        gap:12px!important;
    }

    .fhcrm-ticket-foot i{
        font-size:24px!important;
    }

    .fhcrm-ticket-foot p{
        font-size:10px!important;
        line-height:1.35!important;
    }
}

/* For 768–850px-tall laptop/desktop windows */
@media (min-width:981px) and (max-height:850px){
    .fhcrm-map{
        top:145px!important;
        height:270px!important;
    }

    .fhcrm-features{
        bottom:104px!important;
    }

    .fhcrm-countries{
        bottom:34px!important;
    }

    .fhcrm-ticket{
        grid-template-rows:90px minmax(0,1fr) 92px 58px!important;
    }

    .fhcrm-ticket-head{
        height:90px!important;
        padding:15px 22px!important;
    }

    .fhcrm-login{
        padding:18px 25px 12px!important;
    }

    .fhcrm-form{
        margin-top:16px!important;
    }

    .fhcrm-input{
        height:43px!important;
    }

    .fhcrm-submit{
        height:43px!important;
    }

    .fhcrm-options{
        margin:10px 0 14px!important;
    }

    .fhcrm-info{
        height:92px!important;
    }

    .fhcrm-info-row{
        height:48px!important;
    }

    .fhcrm-info-row.second{
        height:44px!important;
    }

    .fhcrm-ticket-foot{
        height:58px!important;
    }
}
</style>


<style id="fhcrm-balance-polish-v4">
/* =========================================================
   V4 — visual balance / spacing polish
   Goal: keep one-screen fit, remove dead space, improve
   left/right composition without touching authentication.
   ========================================================= */

@media (min-width:981px){

    .fhcrm-shell{
        grid-template-columns:minmax(0,1.36fr) minmax(560px,.96fr)!important;
    }

    /* LEFT: pull map and lower content together */
    .fhcrm-left{
        padding-top:22px!important;
    }

    .fhcrm-logo{
        width:225px!important;
    }

    .fhcrm-copy{
        margin-top:12px!important;
    }

    .fhcrm-copy h1{
        font-size:31px!important;
        letter-spacing:-.04em!important;
    }

    .fhcrm-copy p{
        font-size:12.5px!important;
    }

    .fhcrm-plane{
        top:58px!important;
        right:62px!important;
        font-size:50px!important;
        opacity:.82!important;
    }

    .fhcrm-map{
        top:150px!important;
        left:18px!important;
        right:14px!important;
        height:332px!important;
    }

    .fhcrm-features{
        bottom:118px!important;
    }

    .fhcrm-countries{
        bottom:40px!important;
    }

    /* Make lower blocks slightly denser and visually connected */
    .fhcrm-feature{
        background:rgba(255,255,255,.96)!important;
        box-shadow:0 8px 22px rgba(28,66,112,.06)!important;
    }

    .fhcrm-countries{
        background:rgba(255,255,255,.97)!important;
        box-shadow:0 8px 24px rgba(28,66,112,.05)!important;
    }

    .fhcrm-skyline{
        bottom:0!important;
        opacity:.16!important;
    }

    /* RIGHT: reduce dead space in the sign-in body */
    .fhcrm-ticket{
        grid-template-rows:96px minmax(0,1fr) 100px 64px!important;
    }

    .fhcrm-ticket-main{
        display:grid!important;
        grid-template-columns:minmax(0,1fr) 80px!important;
        align-items:stretch!important;
    }

    .fhcrm-login{
        padding:20px 28px 14px!important;
        display:flex!important;
        flex-direction:column!important;
        justify-content:flex-start!important;
    }

    .fhcrm-login h3{
        font-size:26px!important;
        margin-top:0!important;
    }

    .fhcrm-login>p{
        margin-top:7px!important;
    }

    .fhcrm-form{
        margin-top:18px!important;
    }

    .fhcrm-field{
        margin-bottom:10px!important;
    }

    .fhcrm-options{
        margin:12px 0 16px!important;
    }

    .fhcrm-submit{
        margin-top:0!important;
    }

    /* pull the login stack slightly upward and give more breathing room below button */
    .fhcrm-ticket-main:after{
        content:"";
        display:block;
    }

    .fhcrm-rail{
        padding-top:10px!important;
        padding-bottom:10px!important;
    }

    .fhcrm-barcode{
        height:110px!important;
        margin-bottom:14px!important;
    }

    /* ticket info/footer cleaner proportions */
    .fhcrm-info{
        border-top:1px dashed #adc2da!important;
    }

    .fhcrm-info-row{
        padding-left:18px!important;
        padding-right:18px!important;
    }

    .fhcrm-ticket-foot{
        justify-content:center!important;
        padding-left:24px!important;
        padding-right:24px!important;
    }

    .fhcrm-ticket-foot p{
        font-size:10.5px!important;
    }

    /* Footer copy centered cleanly */
    .fhcrm-copyline{
        left:50%!important;
        transform:translateX(-50%)!important;
        white-space:nowrap!important;
    }
}

/* Extra balancing for common 900–1050px desktop heights */
@media (min-width:981px) and (min-height:851px){
    .fhcrm-map{
        top:154px!important;
        height:350px!important;
    }

    .fhcrm-features{
        bottom:122px!important;
    }

    .fhcrm-countries{
        bottom:42px!important;
    }
}

/* Keep current compact fit on shorter laptops */
@media (min-width:981px) and (max-height:850px){
    .fhcrm-logo{
        width:208px!important;
    }

    .fhcrm-copy h1{
        font-size:29px!important;
    }

    .fhcrm-map{
        top:142px!important;
        height:286px!important;
    }

    .fhcrm-features{
        bottom:104px!important;
    }

    .fhcrm-countries{
        bottom:34px!important;
    }

    .fhcrm-login{
        padding-top:16px!important;
    }

    .fhcrm-form{
        margin-top:14px!important;
    }
}
</style>


<style id="fhcrm-structure-reflow-v5">
/* =========================================================
   V5 — STRUCTURAL REFLOW
   Removes the absolute-positioned desktop composition that
   was creating large blank areas. Authentication untouched.
   ========================================================= */

@media (min-width:981px){

    html,body{
        height:100%;
        overflow:hidden;
    }

    .fhcrm-page{
        height:100vh!important;
        min-height:100vh!important;
        padding:8px 10px!important;
    }

    .fhcrm-shell{
        width:calc(100vw - 20px)!important;
        height:calc(100vh - 16px)!important;
        min-height:0!important;
        max-height:calc(100vh - 16px)!important;
        grid-template-columns:minmax(0,1.38fr) minmax(560px,.92fr)!important;
        overflow:hidden!important;
    }

    /* ================= LEFT — true content flow ================= */
    .fhcrm-left{
        position:relative!important;
        height:100%!important;
        min-height:0!important;
        padding:22px 28px 16px!important;
        display:grid!important;
        grid-template-rows:auto auto minmax(0,1fr) auto auto!important;
        row-gap:10px!important;
        overflow:hidden!important;
    }

    .fhcrm-logo{
        position:static!important;
        width:220px!important;
        max-width:42%!important;
        margin:0!important;
    }

    .fhcrm-copy{
        position:static!important;
        margin:0!important;
        z-index:3!important;
    }

    .fhcrm-copy h1{
        font-size:31px!important;
        line-height:1.02!important;
        margin:0!important;
    }

    .fhcrm-copy p{
        margin:8px 0 0!important;
        font-size:12.5px!important;
    }

    .fhcrm-copy:after{
        margin-top:10px!important;
    }

    .fhcrm-plane{
        top:64px!important;
        right:62px!important;
        font-size:48px!important;
        opacity:.7!important;
    }

    .fhcrm-map{
        position:relative!important;
        inset:auto!important;
        top:auto!important;
        left:auto!important;
        right:auto!important;
        bottom:auto!important;
        height:auto!important;
        min-height:0!important;
        width:100%!important;
        margin:0!important;
        overflow:visible!important;
    }

    .fhcrm-map svg{
        width:100%!important;
        height:100%!important;
        max-height:100%!important;
        display:block!important;
    }

    .fhcrm-features{
        position:static!important;
        inset:auto!important;
        margin:0!important;
        display:grid!important;
        grid-template-columns:repeat(4,minmax(0,1fr))!important;
        gap:10px!important;
    }

    .fhcrm-feature{
        min-height:56px!important;
        padding:8px 9px!important;
    }

    .fhcrm-feature-icon{
        width:34px!important;
        height:34px!important;
        flex-basis:34px!important;
        font-size:15px!important;
    }

    .fhcrm-feature b{
        font-size:8.5px!important;
    }

    .fhcrm-feature small{
        font-size:6.7px!important;
        line-height:1.25!important;
    }

    .fhcrm-countries{
        position:static!important;
        inset:auto!important;
        margin:0!important;
        padding:8px 12px 7px!important;
        border-radius:10px!important;
    }

    .fhcrm-countries-head{
        margin-bottom:6px!important;
        font-size:8.5px!important;
    }

    .fhcrm-country-row{
        gap:6px!important;
    }

    .fhcrm-country{
        font-size:6px!important;
    }

    .fhcrm-flag{
        width:24px!important;
        height:24px!important;
        margin-bottom:3px!important;
        font-size:15px!important;
    }

    .fhcrm-skyline{
        display:none!important;
    }

    .fhcrm-copyline{
        position:absolute!important;
        left:50%!important;
        bottom:4px!important;
        transform:translateX(-50%)!important;
        white-space:nowrap!important;
        font-size:7px!important;
        z-index:20!important;
    }

    /* pin typography in the now-flexible map */
    .fhcrm-pin i{font-size:15px!important}
    .fhcrm-pin span{
        padding:2px 5px!important;
        font-size:6px!important;
    }

    /* ================= RIGHT — proportional ticket flow ================= */
    .fhcrm-right{
        height:100%!important;
        min-height:0!important;
        padding:14px 22px 14px 6px!important;
        display:flex!important;
        align-items:stretch!important;
    }

    .fhcrm-ticket{
        height:100%!important;
        min-height:0!important;
        max-height:100%!important;
        display:grid!important;
        grid-template-rows:96px minmax(0,1fr) auto 64px!important;
        overflow:hidden!important;
    }

    .fhcrm-ticket-head{
        height:auto!important;
        min-height:0!important;
        padding:18px 24px!important;
    }

    .fhcrm-ticket-main{
        min-height:0!important;
        height:auto!important;
        display:grid!important;
        grid-template-columns:minmax(0,1fr) 80px!important;
        overflow:hidden!important;
    }

    .fhcrm-login{
        padding:24px 28px 18px!important;
        display:flex!important;
        flex-direction:column!important;
        justify-content:flex-start!important;
    }

    .fhcrm-login h3{
        font-size:26px!important;
        margin:0!important;
    }

    .fhcrm-login>p{
        margin:8px 0 0!important;
        font-size:11px!important;
    }

    .fhcrm-form{
        margin-top:22px!important;
    }

    .fhcrm-field{
        margin-bottom:11px!important;
    }

    .fhcrm-input{
        height:46px!important;
        font-size:12px!important;
    }

    .fhcrm-options{
        margin:12px 0 17px!important;
        font-size:10px!important;
    }

    .fhcrm-submit{
        height:46px!important;
        font-size:13px!important;
    }

    .fhcrm-rail{
        min-height:0!important;
        padding:10px 0!important;
    }

    .fhcrm-barcode{
        width:28px!important;
        height:108px!important;
        margin-bottom:14px!important;
    }

    .fhcrm-railtext{
        font-size:13px!important;
    }

    .fhcrm-info{
        height:auto!important;
        overflow:visible!important;
    }

    .fhcrm-info-row{
        min-height:0!important;
        height:48px!important;
        padding:0 16px!important;
    }

    .fhcrm-info-row.second{
        height:44px!important;
    }

    .fhcrm-info-item{
        padding:0 8px!important;
    }

    .fhcrm-info-item small{
        margin-bottom:2px!important;
        font-size:6px!important;
    }

    .fhcrm-info-item b{
        font-size:10.5px!important;
    }

    .fhcrm-ticket-foot{
        min-height:0!important;
        height:64px!important;
        padding:9px 20px!important;
        gap:12px!important;
    }

    .fhcrm-ticket-foot i{
        font-size:24px!important;
    }

    .fhcrm-ticket-foot p{
        font-size:10px!important;
        line-height:1.35!important;
    }
}

/* shorter laptop screens */
@media (min-width:981px) and (max-height:820px){
    .fhcrm-left{
        padding-top:16px!important;
        grid-template-rows:auto auto minmax(0,1fr) auto auto!important;
        row-gap:7px!important;
    }

    .fhcrm-logo{width:200px!important}
    .fhcrm-copy h1{font-size:28px!important}
    .fhcrm-copy p{font-size:11.5px!important}

    .fhcrm-feature{
        min-height:50px!important;
        padding:7px!important;
    }

    .fhcrm-countries{
        padding-top:6px!important;
        padding-bottom:6px!important;
    }

    .fhcrm-ticket{
        grid-template-rows:90px minmax(0,1fr) auto 58px!important;
    }

    .fhcrm-login{
        padding:18px 24px 14px!important;
    }

    .fhcrm-form{
        margin-top:16px!important;
    }

    .fhcrm-input,
    .fhcrm-submit{
        height:42px!important;
    }

    .fhcrm-info-row{height:44px!important}
    .fhcrm-info-row.second{height:40px!important}
    .fhcrm-ticket-foot{height:58px!important}
}
</style>


<style id="fhcrm-final-balance-v6">
/* =========================================================
   V6 — FINAL BALANCE PASS
   Goal:
   1) remove the oversized empty white area in the ticket
   2) bring the left map closer to the heading and features
   3) keep the full login page inside one desktop viewport
   Authentication / routes / field names remain untouched.
   ========================================================= */

@media (min-width:981px){

    /* ---------- LEFT SIDE ---------- */
    .fhcrm-left{
        grid-template-rows:auto auto minmax(0,1fr) auto auto!important;
        row-gap:6px!important;
    }

    .fhcrm-copy{
        margin-bottom:-4px!important;
    }

    .fhcrm-map{
        margin-top:-8px!important;
        margin-bottom:-8px!important;
        transform:scale(1.035)!important;
        transform-origin:center center!important;
    }

    .fhcrm-features{
        margin-top:-2px!important;
    }

    .fhcrm-countries{
        margin-top:-2px!important;
    }

    /* ---------- RIGHT SIDE ---------- */

    /* Rebalance the ticket so the info/footer comes higher */
    .fhcrm-ticket{
        grid-template-rows:
            96px
            minmax(0,1fr)
            92px
            62px!important;
    }

    .fhcrm-ticket-main{
        position:relative!important;
    }

    /* Keep login content visually centered in the usable white section,
       instead of stuck at the very top with a huge dead area below. */
    .fhcrm-login{
        justify-content:flex-start!important;
        padding:22px 28px 16px!important;
    }

    .fhcrm-form{
        margin-top:18px!important;
    }

    /* The form itself should occupy a sensible compact block */
    .fhcrm-login .fhcrm-form{
        max-width:none!important;
    }

    .fhcrm-field{
        margin-bottom:10px!important;
    }

    .fhcrm-options{
        margin:11px 0 15px!important;
    }

    .fhcrm-submit{
        margin-bottom:0!important;
    }

    /* Pull the lower ticket sections upward by reducing the visual body
       height with a proportional spacer instead of leaving raw whitespace. */
    .fhcrm-ticket-main{
        min-height:0!important;
    }

    .fhcrm-ticket-main::before{
        content:""!important;
        position:absolute!important;
        left:0!important;
        right:80px!important;
        bottom:0!important;
        height:18%!important;
        min-height:42px!important;
        max-height:78px!important;
        background:
            linear-gradient(
                to bottom,
                rgba(255,255,255,0),
                rgba(247,250,254,.72)
            )!important;
        pointer-events:none!important;
    }

    /* Rail stays visually balanced */
    .fhcrm-rail{
        justify-content:flex-start!important;
        padding-top:18px!important;
    }

    .fhcrm-barcode{
        margin-top:0!important;
    }

    /* Lower rows slightly denser */
    .fhcrm-info-row{
        height:46px!important;
    }

    .fhcrm-info-row.second{
        height:42px!important;
    }

    .fhcrm-info-item small{
        font-size:5.8px!important;
    }

    .fhcrm-info-item b{
        font-size:10.2px!important;
    }

    .fhcrm-ticket-foot{
        height:62px!important;
    }
}

/* Typical 1080p / tall desktop */
@media (min-width:981px) and (min-height:851px){
    .fhcrm-map{
        transform:scale(1.055)!important;
        margin-top:-12px!important;
        margin-bottom:-14px!important;
    }

    .fhcrm-ticket{
        grid-template-rows:
            96px
            minmax(0,1fr)
            92px
            62px!important;
    }

    .fhcrm-login{
        padding-top:24px!important;
    }
}

/* Shorter laptops: preserve single-screen fit */
@media (min-width:981px) and (max-height:850px){
    .fhcrm-map{
        transform:scale(1.02)!important;
        margin-top:-6px!important;
        margin-bottom:-6px!important;
    }

    .fhcrm-ticket{
        grid-template-rows:
            90px
            minmax(0,1fr)
            84px
            56px!important;
    }

    .fhcrm-login{
        padding:16px 24px 12px!important;
    }

    .fhcrm-form{
        margin-top:14px!important;
    }

    .fhcrm-info-row{
        height:42px!important;
    }

    .fhcrm-info-row.second{
        height:38px!important;
    }

    .fhcrm-ticket-foot{
        height:56px!important;
    }
}
</style>


<style id="fhcrm-final-ticket-spacing-v7">
/* =========================================================
   V7 — FINAL RIGHT-TICKET SPACING FIX
   Left side stays as-is.
   The flight details + ticket footer are moved directly
   beneath the login block instead of being forced to the
   bottom of a tall flexible white section.
   Authentication/backend untouched.
   ========================================================= */

@media (min-width:981px){

    /* Ticket no longer gives all remaining height to main */
    .fhcrm-ticket{
        display:flex!important;
        flex-direction:column!important;
        height:100%!important;
    }

    .fhcrm-ticket-head{
        flex:0 0 96px!important;
    }

    /* Main login section gets a deliberate compact height */
    .fhcrm-ticket-main{
        flex:0 0 390px!important;
        height:390px!important;
        min-height:0!important;
        overflow:hidden!important;
    }

    .fhcrm-login{
        height:100%!important;
        box-sizing:border-box!important;
        padding:22px 28px 18px!important;
    }

    /* Remove the previous artificial bottom gradient/spacer */
    .fhcrm-ticket-main::before,
    .fhcrm-ticket-main::after{
        display:none!important;
        content:none!important;
    }

    /* Flight details now follow the form immediately */
    .fhcrm-info{
        flex:0 0 92px!important;
        height:92px!important;
        margin:0!important;
    }

    .fhcrm-info-row{
        height:48px!important;
    }

    .fhcrm-info-row.second{
        height:44px!important;
    }

    /* Footer follows info rows */
    .fhcrm-ticket-foot{
        flex:0 0 64px!important;
        height:64px!important;
        margin:0!important;
    }

    /*
      Any remaining ticket height becomes a subtle bottom extension,
      not a giant empty block between SIGN IN and flight details.
    */
    .fhcrm-ticket::after{
        content:""!important;
        display:block!important;
        flex:1 1 auto!important;
        min-height:0!important;
        background:
            linear-gradient(180deg,#f8fbff 0%,#edf5fd 100%)!important;
        border-top:1px solid rgba(166,191,218,.35)!important;
    }
}

/* Taller desktop: slightly more breathing room in login section */
@media (min-width:981px) and (min-height:900px){
    .fhcrm-ticket-main{
        flex-basis:410px!important;
        height:410px!important;
    }
}

/* Typical laptop heights */
@media (min-width:981px) and (max-height:850px){
    .fhcrm-ticket-head{
        flex-basis:90px!important;
    }

    .fhcrm-ticket-main{
        flex-basis:350px!important;
        height:350px!important;
    }

    .fhcrm-info{
        flex-basis:84px!important;
        height:84px!important;
    }

    .fhcrm-info-row{
        height:44px!important;
    }

    .fhcrm-info-row.second{
        height:40px!important;
    }

    .fhcrm-ticket-foot{
        flex-basis:56px!important;
        height:56px!important;
    }
}
</style>


<style id="fhcrm-final-bottom-close-v8">
/* =========================================================
   V8 — FINAL BOTTOM CLOSE
   Removes the extra light-blue extension under the ticket
   footer while preserving the working V7 layout.
   Left side and authentication remain untouched.
   ========================================================= */

@media (min-width:981px){

    /* Remove V7's flexible decorative extension */
    .fhcrm-ticket::after{
        display:none!important;
        content:none!important;
    }

    /* Let the ticket end exactly after its footer */
    .fhcrm-ticket{
        height:auto!important;
        min-height:0!important;
        max-height:none!important;
        align-self:center!important;
    }

    /* Keep the right panel centered vertically in the viewport */
    .fhcrm-right{
        align-items:center!important;
    }

    /* Preserve exact section sizes from V7 */
    .fhcrm-ticket-head{
        flex:0 0 96px!important;
    }

    .fhcrm-ticket-main{
        flex:0 0 390px!important;
        height:390px!important;
    }

    .fhcrm-info{
        flex:0 0 92px!important;
        height:92px!important;
    }

    .fhcrm-ticket-foot{
        flex:0 0 64px!important;
        height:64px!important;
        border-radius:0 0 20px 20px!important;
    }
}

@media (min-width:981px) and (min-height:900px){
    .fhcrm-ticket-main{
        flex-basis:410px!important;
        height:410px!important;
    }
}

@media (min-width:981px) and (max-height:850px){
    .fhcrm-ticket-head{
        flex-basis:90px!important;
    }

    .fhcrm-ticket-main{
        flex-basis:350px!important;
        height:350px!important;
    }

    .fhcrm-info{
        flex-basis:84px!important;
        height:84px!important;
    }

    .fhcrm-ticket-foot{
        flex-basis:56px!important;
        height:56px!important;
    }
}
</style>


<style id="fhcrm-world-map-layout-fix-v1">
@media (min-width:981px){
    .fhcrm-map{
        align-self:start!important;
        height:clamp(280px,34vh,350px)!important;
        min-height:280px!important;
        margin-top:-8px!important;
        margin-bottom:-8px!important;
        transform:none!important;
    }

    .fhcrm-map svg{
        width:100%!important;
        height:100%!important;
        display:block!important;
    }
}
</style>

</head>
<body>
<div class="fhcrm-page"><div class="fhcrm-shell">
<section class="fhcrm-left">
<img class="fhcrm-logo" src="/assets/img/fareshunt-logo-transparent.png" alt="FaresHunt">
<div class="fhcrm-copy"><h1>One World.<span>Endless Journeys.</span></h1><p>Smart Travel Business Management Platform</p></div>
<div class="fhcrm-plane"><i class="fa-solid fa-plane"></i></div>
<div class="fhcrm-map">
<svg viewBox="0 0 900 430" preserveAspectRatio="xMidYMid meet" aria-hidden="true"><defs><pattern id="fhcrmDots" x="0" y="0" width="8" height="8" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="1.5" fill="#b7d7ff"/></pattern></defs><path fill="url(#fhcrmDots)" d="M28,126 C75,75 145,67 197,81 C250,95 272,116 301,129 C271,146 233,141 218,165 C195,194 174,221 155,244 C132,234 107,207 93,190 C73,166 47,157 28,126 Z M190,252 C214,254 244,277 251,313 C258,347 242,386 217,408 C193,381 179,340 179,306 C179,283 183,268 190,252 Z M380,105 C420,77 475,70 525,83 C554,91 573,111 596,119 C617,127 650,112 675,118 C705,125 726,143 745,156 C719,168 688,168 664,184 C638,201 625,222 603,229 C579,237 552,219 531,222 C511,225 505,250 478,250 C451,249 433,231 420,209 C405,184 386,167 365,156 C355,141 365,117 380,105 Z M414,180 C446,194 468,219 476,253 C484,288 463,334 434,356 C402,338 386,302 389,266 C391,230 400,203 414,180 Z M624,295 C652,278 687,283 710,303 C733,322 737,350 722,369 C697,380 665,376 643,358 C622,340 614,316 624,295 Z M774,308 C811,293 848,304 868,327 C859,348 840,366 814,369 C788,364 770,344 774,308 Z"/><path d="M110 180 C210 130 260 230 360 190 C470 145 555 195 650 160 C710 137 760 165 804 192" fill="none" stroke="#76acf0" stroke-width="2" stroke-dasharray="5 8"/><path d="M520 290 C590 225 655 245 695 288 C729 326 772 333 820 306" fill="none" stroke="#76acf0" stroke-width="2" stroke-dasharray="5 8"/></svg>
<div class="fhcrm-pin p1"><i class="fa-solid fa-location-dot"></i><span>Canada</span></div><div class="fhcrm-pin p2"><i class="fa-solid fa-location-dot"></i><span>United States</span></div><div class="fhcrm-pin blue p3"><i class="fa-solid fa-location-dot"></i><span>Brazil</span></div><div class="fhcrm-pin blue p4"><i class="fa-solid fa-location-dot"></i><span>United Kingdom</span></div><div class="fhcrm-pin p5"><i class="fa-solid fa-location-dot"></i><span>France</span></div><div class="fhcrm-pin blue p6"><i class="fa-solid fa-location-dot"></i><span>Germany</span></div><div class="fhcrm-pin blue p7"><i class="fa-solid fa-location-dot"></i><span>UAE</span></div><div class="fhcrm-pin p8"><i class="fa-solid fa-location-dot"></i><span>India</span></div><div class="fhcrm-pin p9"><i class="fa-solid fa-location-dot"></i><span>China</span></div><div class="fhcrm-pin blue p10"><i class="fa-solid fa-location-dot"></i><span>Japan</span></div><div class="fhcrm-pin blue p11"><i class="fa-solid fa-location-dot"></i><span>Singapore</span></div><div class="fhcrm-pin p12"><i class="fa-solid fa-location-dot"></i><span>Australia</span></div>
</div>
<div class="fhcrm-features"><div class="fhcrm-feature"><span class="fhcrm-feature-icon"><i class="fa-solid fa-globe"></i></span><div><b>Global Reach</b><small>Manage worldwide<br>travel operations</small></div></div><div class="fhcrm-feature"><span class="fhcrm-feature-icon"><i class="fa-solid fa-shield-halved"></i></span><div><b>Secure &amp; Reliable</b><small>Enterprise grade<br>security</small></div></div><div class="fhcrm-feature"><span class="fhcrm-feature-icon"><i class="fa-solid fa-chart-column"></i></span><div><b>Powerful Analytics</b><small>Real-time insights<br>for smarter decisions</small></div></div><div class="fhcrm-feature"><span class="fhcrm-feature-icon"><i class="fa-solid fa-headset"></i></span><div><b>24/7 Support</b><small>We're here<br>when you need us</small></div></div></div>
<div class="fhcrm-countries"><div class="fhcrm-countries-head"><span><i class="fa-solid fa-globe"></i>&nbsp;&nbsp;POPULAR COUNTRIES</span><a href="/">View All &nbsp;<i class="fa-solid fa-arrow-right"></i></a></div><div class="fhcrm-country-row"><div class="fhcrm-country"><span class="fhcrm-flag">🇺🇸</span>United States</div><div class="fhcrm-country"><span class="fhcrm-flag">🇬🇧</span>United Kingdom</div><div class="fhcrm-country"><span class="fhcrm-flag">🇨🇦</span>Canada</div><div class="fhcrm-country"><span class="fhcrm-flag">🇫🇷</span>France</div><div class="fhcrm-country"><span class="fhcrm-flag">🇩🇪</span>Germany</div><div class="fhcrm-country"><span class="fhcrm-flag">🇦🇪</span>UAE</div><div class="fhcrm-country"><span class="fhcrm-flag">🇸🇬</span>Singapore</div><div class="fhcrm-country"><span class="fhcrm-flag">🇦🇺</span>Australia</div><div class="fhcrm-country"><span class="fhcrm-flag">🇮🇳</span>India</div></div></div>
<div class="fhcrm-skyline"><i class="fa-solid fa-bridge"></i><i class="fa-solid fa-building-columns"></i><i class="fa-solid fa-tower-observation"></i><i class="fa-solid fa-landmark"></i><i class="fa-solid fa-city"></i><i class="fa-solid fa-building"></i></div>
</section>
<section class="fhcrm-right"><div class="fhcrm-ticket">
<div class="fhcrm-ticket-head"><div class="fhcrm-ticket-title"><span class="fhcrm-ticket-plane"><i class="fa-solid fa-plane"></i></span><div><h2>BOARDING PASS</h2><p>WELCOME ABOARD</p></div></div><div class="fhcrm-code"><strong>FH</strong><span>CRM {{ date('Y') }}</span></div></div>
<div class="fhcrm-ticket-main"><div class="fhcrm-login"><h3>SIGN IN</h3><p>Access your FaresHunt CRM account</p>
@if($errors->any())<div class="fhcrm-errors">{{ $errors->first() }}</div>@endif
<form class="fhcrm-form" method="POST" action="{{ route('crm.authenticate') }}">@csrf
<div class="fhcrm-field"><i class="fa-regular fa-user"></i><input class="fhcrm-input" type="text" name="username" value="{{ old('username') }}" placeholder="Username or Email" autocomplete="username" required autofocus></div>
<div class="fhcrm-field"><i class="fa-solid fa-lock"></i><input class="fhcrm-input" id="fhcrmPassword" type="password" name="password" placeholder="Password" autocomplete="current-password" required><button class="fhcrm-eye" id="fhcrmEye" type="button" aria-label="Show password"><i class="fa-regular fa-eye"></i></button></div>
<div class="fhcrm-options"><label class="fhcrm-remember"><input type="checkbox" name="remember" value="1"><span>Remember me</span></label><span class="fhcrm-forgot">Secure staff access</span></div>
<button class="fhcrm-submit" type="submit"><i class="fa-solid fa-plane"></i>SIGN IN</button></form></div>
<aside class="fhcrm-rail"><div class="fhcrm-barcode"></div><div class="fhcrm-railtext">FARES HUNT CRM <small>HUNT THE BEST FARES</small></div></aside></div>
<div class="fhcrm-info"><div class="fhcrm-info-row"><div class="fhcrm-info-item"><small><i class="fa-solid fa-plane"></i>&nbsp; FLIGHT</small><b>FH &nbsp; {{ date('Y') }}</b></div><div class="fhcrm-info-item"><small><i class="fa-solid fa-id-badge"></i>&nbsp; GATE</small><b>CRM</b></div><div class="fhcrm-info-item"><small><i class="fa-solid fa-chair"></i>&nbsp; SEAT</small><b>01A</b></div><div class="fhcrm-info-item"><small><i class="fa-solid fa-couch"></i>&nbsp; CLASS</small><b class="red">BUSINESS</b></div></div><div class="fhcrm-info-row second"><div class="fhcrm-info-item"><small><i class="fa-solid fa-globe"></i>&nbsp; DESTINATION</small><b>Worldwide</b></div><div class="fhcrm-info-item"><small><i class="fa-regular fa-clock"></i>&nbsp; BOARDING TIME</small><b>24/7 Access</b></div><div class="fhcrm-info-item"><small><i class="fa-regular fa-circle-check"></i>&nbsp; STATUS</small><b>Ready to Go</b></div></div></div>
<div class="fhcrm-ticket-foot"><i class="fa-solid fa-plane"></i><p>Connecting the world,<em>one journey at a time.</em></p></div>
</div></section>
<div class="fhcrm-copyline">© {{ date('Y') }} Fares Hunt CRM. All rights reserved. &nbsp; | &nbsp; <i class="fa-solid fa-shield-halved"></i> Built for Travel Professionals</div>
</div></div>
<script>document.addEventListener('DOMContentLoaded',function(){const p=document.getElementById('fhcrmPassword'),b=document.getElementById('fhcrmEye');if(!p||!b)return;b.addEventListener('click',function(){const s=p.type==='password';p.type=s?'text':'password';const i=b.querySelector('i');if(i)i.className=s?'fa-regular fa-eye-slash':'fa-regular fa-eye';b.setAttribute('aria-label',s?'Hide password':'Show password')})});</script>
</body></html>