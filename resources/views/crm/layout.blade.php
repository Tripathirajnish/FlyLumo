<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>FaresHunt CRM · Operations</title>
<link href="/assets/css/bootstrap.min.css" rel="stylesheet"><link href="/assets/css/fontawesome.css" rel="stylesheet"><link href="/assets/css/crm-booking-status-v1.css" rel="stylesheet"><link href="/assets/css/crm-email-communications-v1.css" rel="stylesheet"><link href="/assets/css/crm-email-templates-v2.css" rel="stylesheet">
<style>
:root{--crm-bg:#f5f7fb;--crm-card:#fff;--crm-line:#e1e7ef;--crm-text:#0b1830;--crm-muted:#718099;--crm-blue:#2459df;--crm-blue2:#3e73ef;--crm-red:#e43b45;--crm-green:#14a65a;--crm-orange:#f97316;--crm-pink:#db2777;--crm-purple:#7c3aed;--crm-shadow:0 14px 35px rgba(22,43,78,.055)}*{box-sizing:border-box}body{margin:0;background:var(--crm-bg);font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;color:var(--crm-text);font-size:13px}.crm-shell{min-height:100vh}.crm-sidebar{position:fixed;left:0;top:0;bottom:0;width:282px;background:#fff;border-right:1px solid var(--crm-line);z-index:50;display:flex;flex-direction:column}.crm-brand{height:76px;display:flex;align-items:center;padding:0 22px;border-bottom:1px solid #edf1f6;font-size:20px;font-weight:850;letter-spacing:-.02em}.crm-brand-icon{width:38px;height:38px;border-radius:12px;background:linear-gradient(145deg,#2459df,#4a7df0);color:#fff;display:grid;place-items:center;margin-right:12px;box-shadow:0 9px 22px rgba(36,89,223,.22)}.crm-org{margin:14px 14px 9px;padding:12px;border-radius:14px;background:#f0f4f9;display:flex;gap:10px;align-items:center}.crm-org i{color:#64748b}.crm-org b{display:block;font-size:13px}.crm-org small{display:block;color:#7d8ba0}.crm-nav{padding:8px 12px;overflow:auto}.crm-nav-label{font-size:10px;letter-spacing:.14em;color:#8b99ac;text-transform:uppercase;padding:15px 9px 7px}.crm-link{display:flex;align-items:center;gap:13px;padding:11px 12px;border-radius:10px;text-decoration:none;color:#41536f;font-weight:600;margin:2px 0}.crm-link i{width:20px;text-align:center;font-size:15px;color:#687a94}.crm-link:hover{background:#f6f8fb;color:#173256}.crm-link.active{background:#edf4ff;color:#2459df}.crm-link.active i{color:#2459df}.crm-link .count{margin-left:auto;border-radius:999px;background:#ff7a12;color:#fff;font-size:10px;min-width:21px;height:21px;display:grid;place-items:center;padding:0 6px}.crm-user{margin-top:auto;border-top:1px solid #edf1f6;padding:14px}.crm-user-box{display:flex;align-items:center;gap:10px}.avatar{width:38px;height:38px;border-radius:50%;display:grid;place-items:center;background:#2459df;color:#fff;font-weight:800}.crm-user-box b{font-size:12px}.crm-user-box small{color:#8090a5;display:block}.crm-logout{margin-left:auto;border:0;background:transparent;color:#7a889b}.crm-main{margin-left:282px;min-height:100vh}.crm-top{height:76px;padding:0 28px;display:flex;align-items:center;justify-content:space-between;background:#fff;border-bottom:1px solid #e7ebf1;position:sticky;top:0;z-index:30}.crm-top-left b{font-size:15px}.crm-top-left small{display:block;color:#8491a4}.crm-top-actions{display:flex;align-items:center;gap:10px}.crm-date-tabs{display:flex;background:#f4f6f9;border-radius:12px;padding:4px}.crm-date-tabs span{padding:8px 11px;color:#718099}.crm-date-tabs .active{background:#fff;border-radius:9px;color:#162843;font-weight:700;box-shadow:0 2px 7px rgba(10,25,50,.06)}.crm-content{padding:28px;max-width:1700px;margin:0 auto}.title{font-size:28px;letter-spacing:-.035em;margin:0;font-weight:500}.sub{font-size:14px;color:#718099;margin-top:4px}.crm-page-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:22px}.crm-card,.card{background:#fff;border:1px solid var(--crm-line)!important;border-radius:16px!important;box-shadow:var(--crm-shadow)!important;overflow:hidden}.crm-card-head,.card .head{min-height:54px;padding:14px 17px;border-bottom:1px solid #edf1f5;display:flex;align-items:center;justify-content:space-between;gap:12px}.crm-card-head h3{font-size:13px;margin:0;font-weight:750}.crm-card-body{padding:17px}.crm-kpis{display:grid;grid-template-columns:2fr repeat(2,1fr);gap:16px;margin:20px 0}.crm-kpi{background:#fff;border:1px solid var(--crm-line);border-radius:16px;padding:22px;min-height:178px;position:relative;overflow:hidden}.crm-kpi.hero{background:linear-gradient(145deg,#1f55dc,#3f74e9);color:#fff;border:0}.crm-kpi.hero:after{content:"";position:absolute;width:520px;height:180px;border-radius:50%;background:rgba(255,255,255,.08);right:-120px;bottom:-85px}.crm-kpi .label{font-size:11px;color:#74839a;text-transform:uppercase}.crm-kpi.hero .label,.crm-kpi.hero small{color:#dbe7ff}.crm-kpi .value{font-size:34px;margin:10px 0 3px;letter-spacing:-.035em}.crm-kpi.hero .value{font-size:42px}.crm-kpi .trend{color:#16a05b;font-size:12px}.crm-kpi.hero .trend{color:#9cffc7}.crm-mini-icon{width:38px;height:38px;border-radius:12px;background:#eff5ff;color:#2459df;display:grid;place-items:center}.crm-grid4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}.crm-metric{background:#fff;border:1px solid var(--crm-line);border-radius:16px;padding:18px;min-height:142px}.crm-metric .value{font-size:27px;margin-top:15px}.crm-metric .label{font-size:10px;color:#74839a;text-transform:uppercase}.crm-lower{display:grid;grid-template-columns:minmax(0,2fr) minmax(320px,.85fr);gap:16px;margin-top:16px}.crm-chart-wrap{display:grid;grid-template-columns:1.65fr .85fr;gap:16px}.crm-chart{height:210px;padding:12px}.crm-chart svg{width:100%;height:100%}.crm-bars{display:grid;gap:13px}.crm-bar-row{display:grid;grid-template-columns:90px 1fr 30px;align-items:center;gap:8px;font-size:11px}.crm-bar{height:7px;border-radius:999px;background:#edf1f6;overflow:hidden}.crm-bar span{display:block;height:100%;border-radius:999px}.crm-alert{display:flex;gap:11px;padding:12px 0;border-bottom:1px solid #edf1f5}.crm-alert:last-child{border:0}.crm-alert-icon{width:34px;height:34px;border-radius:10px;background:#fff1f0;color:#e34a43;display:grid;place-items:center;flex:0 0 auto}.crm-alert b{display:block;font-size:11px}.crm-alert small{display:block;color:#7e8ba0;margin-top:3px}.crm-table,.table{width:100%;border-collapse:collapse;margin:0!important}.crm-table th,.table th{padding:12px 14px;font-size:9px;color:#7f8da2;text-transform:uppercase;letter-spacing:.04em;background:#fafbfc;border-bottom:1px solid #e8edf3}.crm-table td,.table td{padding:13px 14px;border-bottom:1px solid #edf1f5;vertical-align:middle;font-size:11px}.crm-table tr:hover td,.table tbody tr:hover td{background:#fbfcfe}.booking-link{font-weight:800;color:#173250;text-decoration:none}.soft{display:inline-flex;padding:5px 9px;border-radius:999px;background:#f1f4f8;color:#52647c;font-size:9px;font-weight:700}.soft.blue{background:#eef5ff;color:#2459df}.soft.amber{background:#fff5df;color:#bb6b08}.soft.green{background:#eaf8f0;color:#17824c}.soft.red{background:#fff0f0;color:#d6383f}.row-action{width:30px;height:30px;border-radius:9px;display:grid;place-items:center;background:#f3f6fa;color:#51647f;text-decoration:none}.search,.form-control,.form-select{border:1px solid #dbe3ed!important;border-radius:10px!important;min-height:40px!important;box-shadow:none!important;font-size:12px!important}.search{padding:0 12px;min-width:270px}.btn{border-radius:10px!important;font-size:11px!important;font-weight:700!important;padding:9px 14px!important}.btn-primary{background:#2459df!important;border-color:#2459df!important}.btn-danger{background:#d51d3f!important;border-color:#d51d3f!important}.status-pill{padding:7px 11px;border-radius:999px;background:#eff3f7;font-weight:700;font-size:11px}.status-fresh{background:#eaf8f0;color:#167d49}.crm-back{color:#64748b;text-decoration:none;font-size:11px}.eyebrow{font-size:9px;letter-spacing:.1em;text-transform:uppercase;color:#8b98a9;display:block;margin-bottom:3px}.module-icon{width:38px;height:38px;border-radius:11px;background:#eff5ff;color:#2459df;display:grid;place-items:center}.detail-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}.detail-item,.json-row{border:1px solid #e8edf3;background:#fbfcfe;border-radius:11px;padding:12px}.detail-item span,.json-row span{display:block;color:#8290a4;font-size:9px;text-transform:uppercase}.detail-item strong,.json-row strong{font-size:11px;display:block;margin-top:5px;word-break:break-word}.money{font-size:19px!important;color:#2459df}.json-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:9px}.data-box{padding:13px;border:1px solid #e7ecf2;background:#fbfcfe;border-radius:11px;white-space:pre-wrap;word-break:break-word}.payment-card-safe{border-radius:16px;background:linear-gradient(145deg,#0b1b33,#17385d);padding:18px;color:#fff}.card-digits{font-size:20px;letter-spacing:.08em;margin:28px 0 15px}.safe-note{font-size:10px;color:#728198;margin:12px 0 0}.related-booking{display:block;padding:12px 16px;border-top:1px solid #edf1f5;text-decoration:none;color:#1c304c}.related-booking span{font-weight:800}.related-booking small{display:block;color:#7c899d;margin-top:3px}.crm-filterbar{display:flex;gap:9px;align-items:center;flex-wrap:wrap}.crm-stat-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px}.crm-stat-small{padding:16px;border:1px solid var(--crm-line);background:#fff;border-radius:14px}.crm-stat-small b{font-size:21px;display:block}.crm-stat-small span{color:#78879a;font-size:10px}.timeline{display:grid;gap:10px}.timeline-item{display:grid;grid-template-columns:34px 1fr;gap:10px}.timeline-dot{width:30px;height:30px;border-radius:10px;background:#eef5ff;color:#2459df;display:grid;place-items:center}.timeline-body{border:1px solid #e6ebf2;border-radius:11px;padding:11px}.timeline-body small{color:#8190a4}.empty{padding:35px!important;text-align:center;color:#8794a6}.crm-success{background:#eaf8f0;color:#167b49;border:1px solid #ccebd9;padding:11px 14px;border-radius:11px;margin-bottom:15px}.crm-danger{background:#fff1f1;color:#bf2e37;border:1px solid #f4d0d2;padding:11px 14px;border-radius:11px;margin-bottom:15px}
@media(max-width:1100px){.crm-sidebar{width:228px}.crm-main{margin-left:228px}.crm-kpis{grid-template-columns:1fr 1fr}.crm-kpi.hero{grid-column:1/-1}.crm-grid4{grid-template-columns:1fr 1fr}.crm-lower,.crm-chart-wrap{grid-template-columns:1fr}.detail-grid{grid-template-columns:1fr 1fr}}
@media(max-width:760px){.crm-sidebar{transform:translateX(-100%)}.crm-main{margin-left:0}.crm-content{padding:18px}.crm-top{padding:0 18px}.crm-date-tabs{display:none}.crm-kpis,.crm-grid4,.crm-stat-strip{grid-template-columns:1fr}.detail-grid,.json-grid{grid-template-columns:1fr}.crm-page-head{flex-direction:column}}
.crm-headline{display:flex;justify-content:space-between;align-items:end;gap:20px;margin-bottom:18px}.crm-headline h1{font-size:28px;font-weight:500;margin:0;letter-spacing:-.035em}.crm-headline p{color:#718099;margin:5px 0 0;font-size:14px}.crm-range{display:flex;background:#f0f3f7;border-radius:12px;padding:4px}.crm-range button{border:0;background:transparent;padding:8px 10px;color:#718099;font-size:10px}.crm-range .active{background:#fff;border-radius:9px;color:#152945;box-shadow:0 2px 8px rgba(12,26,45,.06)}.crm-metric-grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:16px}.crm-revenue{border-radius:18px;background:linear-gradient(145deg,#2158df,#3f73e9);color:#fff;padding:22px;min-height:252px;position:relative;overflow:hidden}.crm-revenue:after{content:"";position:absolute;width:620px;height:220px;border-radius:50%;background:rgba(255,255,255,.08);right:-160px;bottom:-105px}.crm-revenue .eyebrow{color:#dce7ff;font-size:11px}.crm-revenue .money{font-size:45px;margin:12px 0 3px;color:#fff!important}.crm-revenue .trend{color:#9cffc5;font-size:12px}.crm-line{position:absolute;left:22px;right:22px;bottom:22px;height:64px;z-index:2}.crm-line svg{width:100%;height:100%}.crm-smallmetric{padding:20px;min-height:252px}.metric-icon{width:40px;height:40px;border-radius:12px;background:#eff5ff;color:#2459df;display:grid;place-items:center}.crm-smallmetric .label,.crm-kpi .label{font-size:10px;text-transform:uppercase;color:#76869c}.crm-smallmetric .value,.crm-kpi .value{font-size:30px;margin:7px 0}.crm-smallmetric .delta{font-size:11px;color:#16a05b}.crm-smallmetric.warn{border-color:#f8caca!important}.crm-kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-top:16px}.crm-kpi{padding:18px;min-height:164px}.crm-kpi-top{display:flex;justify-content:space-between;align-items:center}.mi-red{background:#fff0f0;color:#e23d45}.mi-pink{background:#fff0f7;color:#db2777}.mi-orange{background:#fff5e9;color:#f97316}.mi-green{background:#eaf9f1;color:#15a55a}.crm-kpi small{color:#7f8da1}@media(max-width:1100px){.crm-metric-grid{grid-template-columns:1fr 1fr}.crm-revenue{grid-column:1/-1}.crm-kpi-grid{grid-template-columns:1fr 1fr}}@media(max-width:760px){.crm-metric-grid,.crm-kpi-grid{grid-template-columns:1fr}.crm-revenue{grid-column:auto}.crm-headline{flex-direction:column;align-items:flex-start}.crm-range{display:none}}
</style>
<link href="/assets/css/crm-website-cms-v1.css" rel="stylesheet">
<link href="/assets/css/crm-contact-settings-v1.css" rel="stylesheet">
<link href="/assets/css/crm-visual-footer-manager-v1.css" rel="stylesheet">
<link href="/assets/css/crm-visual-content-manager-v1.css" rel="stylesheet">
<link href="/assets/css/crm-visual-pages-manager-v1.css" rel="stylesheet">
<link href="/assets/css/crm-dashboard-v4.css" rel="stylesheet">
<link href="/assets/css/crm-booking-management-v1.css" rel="stylesheet">
<link href="/assets/css/crm-booking-management-premium-v2.css" rel="stylesheet">
<link href="/assets/css/crm-booking-management-premium-v3.css" rel="stylesheet">
<link href="/assets/css/crm-lead-management-premium-v1.css" rel="stylesheet">
<link href="/assets/css/crm-customer-management-premium-v1.css" rel="stylesheet">
<link href="/assets/css/crm-payments-premium-v1.css" rel="stylesheet">
<link href="/assets/css/crm-team-access-v1.css" rel="stylesheet">

<style id="crm-universal-actions-v1">
.crm-ua-actions{display:inline-flex;gap:5px;margin-left:7px;vertical-align:middle;opacity:.72;transition:.15s}
*:hover>.crm-ua-actions,.crm-ua-actions:hover{opacity:1}
.crm-ua-btn{border:1px solid #dfe6ef;background:#fff;color:#60728b;width:25px;height:25px;border-radius:7px;display:inline-grid;place-items:center;padding:0!important;font-size:10px;cursor:pointer}
.crm-ua-btn:hover{color:#2459df;border-color:#bfd0f5;background:#f5f8ff}
.crm-ua-btn.danger:hover{color:#c92d42;border-color:#f0b9c2;background:#fff5f6}
.crm-ua-field{position:relative}
.crm-ua-form-tools{display:flex;gap:5px;margin-top:5px;justify-content:flex-end}
.crm-ua-form-tools button{border:0;background:#f3f6fa;color:#61738d;border-radius:7px;padding:5px 8px;font-size:9px;font-weight:700}
.crm-ua-form-tools button:hover{background:#eaf1ff;color:#2459df}
.crm-ua-toast{position:fixed;right:24px;bottom:24px;z-index:9999;background:#102b4d;color:#fff;padding:11px 14px;border-radius:10px;box-shadow:0 12px 32px rgba(9,30,66,.24);font-size:11px;display:none}
.crm-ua-modal-backdrop{position:fixed;inset:0;background:rgba(9,22,40,.48);z-index:9990;display:none;align-items:center;justify-content:center;padding:20px}
.crm-ua-modal{width:min(520px,100%);background:#fff;border-radius:18px;box-shadow:0 25px 70px rgba(10,28,54,.25);overflow:hidden}
.crm-ua-modal-head{padding:17px 19px;border-bottom:1px solid #e8edf3;display:flex;align-items:center;justify-content:space-between}
.crm-ua-modal-head h3{font-size:15px;margin:0}.crm-ua-modal-body{padding:18px}.crm-ua-modal-actions{padding:14px 18px;border-top:1px solid #e8edf3;display:flex;gap:8px;justify-content:flex-end}
.crm-ua-record-actions{display:flex;gap:7px;align-items:center;flex-wrap:wrap}
.crm-ua-archive{border-color:#f0c3ca!important;color:#b52f43!important;background:#fff7f8!important}
.crm-ua-protected{font-size:9px;color:#8795a8;margin-left:5px}
</style>


<style id="fareshunt-crm-global-brand-v1">
:root{
  --fh-crm-navy:#071f4f;
  --fh-crm-navy-2:#0c316f;
  --fh-crm-red:#e3194a;
  --fh-crm-red-dark:#c91541;
  --fh-crm-blue:#1f5bd8;
  --fh-crm-bg:#f4f8fd;
  --fh-crm-card:#ffffff;
  --fh-crm-line:#dce6f2;
  --fh-crm-muted:#73839b;
  --fh-crm-shadow:0 14px 34px rgba(10,40,82,.06);
}

body{
  background:
    radial-gradient(circle at 100% 0,rgba(37,93,211,.05),transparent 25%),
    var(--fh-crm-bg)!important;
}

/* ---------------- SIDEBAR ---------------- */
.crm-sidebar{
  width:282px!important;
  background:linear-gradient(180deg,#ffffff 0%,#fbfdff 100%)!important;
  border-right:1px solid #d9e5f2!important;
  box-shadow:8px 0 34px rgba(8,38,82,.035)!important;
}

.crm-main{
  margin-left:282px!important;
}

.crm-brand.fh-crm-brand{
  height:82px!important;
  padding:0 18px!important;
  display:flex!important;
  align-items:center!important;
  gap:10px!important;
  border-bottom:1px solid #e6edf5!important;
  background:#fff!important;
}

.fh-crm-brand-logo{
  width:112px!important;
  height:42px!important;
  object-fit:contain!important;
  object-position:left center!important;
  display:block!important;
}

.fh-crm-brand-copy{
  min-width:0;
  padding-left:10px;
  border-left:1px solid #dce5f0;
  line-height:1;
}

.fh-crm-brand-copy strong{
  display:block;
  color:var(--fh-crm-navy);
  font-size:12px;
  font-weight:850;
  letter-spacing:.04em;
}

.fh-crm-brand-copy small{
  display:block;
  margin-top:4px;
  color:var(--fh-crm-red);
  font-size:8px;
  font-weight:800;
  letter-spacing:.18em;
}

.crm-org.fh-crm-org{
  position:relative;
  margin:14px 14px 10px!important;
  padding:13px 12px!important;
  min-height:66px;
  border:1px solid #dbe6f2;
  border-radius:15px!important;
  background:
    radial-gradient(circle at 100% 0,rgba(35,89,216,.08),transparent 48%),
    linear-gradient(135deg,#f8fbff,#f1f6fc)!important;
}

.fh-crm-org-icon{
  width:36px;height:36px;flex:0 0 36px;
  display:grid;place-items:center;
  border-radius:11px;
  background:linear-gradient(145deg,var(--fh-crm-navy-2),#164e9e);
  color:#fff;
  box-shadow:0 8px 18px rgba(9,54,120,.16);
}

.crm-org.fh-crm-org b{
  color:var(--fh-crm-navy)!important;
  font-size:12px!important;
  font-weight:800!important;
}

.crm-org.fh-crm-org small{
  margin-top:3px;
  color:#78889d!important;
  font-size:9px!important;
}

.fh-crm-live{
  margin-left:auto;
  align-self:flex-start;
  display:inline-flex;
  align-items:center;
  gap:4px;
  color:#2b8e62;
  font-size:7px;
  font-weight:800;
  text-transform:uppercase;
  letter-spacing:.06em;
}

.fh-crm-live i{font-size:5px;color:#38bf7b}

.crm-nav{
  padding:7px 11px 10px!important;
}

.crm-nav-label{
  padding:15px 10px 7px!important;
  color:#8a98aa!important;
  font-size:9px!important;
  font-weight:800!important;
  letter-spacing:.15em!important;
}

.crm-link{
  min-height:42px;
  padding:10px 11px!important;
  gap:12px!important;
  margin:2px 0!important;
  border:1px solid transparent;
  border-radius:11px!important;
  color:#425875!important;
  font-size:11.5px!important;
  font-weight:650!important;
  transition:.16s ease;
}

.crm-link i{
  width:21px!important;
  color:#71849d!important;
  font-size:14px!important;
}

.crm-link:hover{
  background:#f5f8fc!important;
  border-color:#e3eaf3!important;
  color:var(--fh-crm-navy)!important;
  transform:translateX(1px);
}

.crm-link.active{
  position:relative;
  background:linear-gradient(90deg,#edf4ff,#f7faff)!important;
  border-color:#d8e7fb!important;
  color:#1554c5!important;
  font-weight:800!important;
}

.crm-link.active:before{
  content:"";
  position:absolute;
  left:-11px;top:8px;bottom:8px;
  width:4px;border-radius:0 4px 4px 0;
  background:var(--fh-crm-red);
}

.crm-link.active i{color:#1f5ed8!important}

.crm-link .count{
  background:linear-gradient(145deg,var(--fh-crm-red),#ff4f73)!important;
  box-shadow:0 5px 12px rgba(227,25,74,.18);
}

/* ---------------- USER / SIGNOUT ---------------- */
.crm-user{
  padding:12px 14px!important;
  border-top:1px solid #e7edf5!important;
  background:#fff;
}

.avatar{
  width:38px!important;height:38px!important;
  background:linear-gradient(145deg,var(--fh-crm-navy-2),#1f5bd8)!important;
  box-shadow:0 7px 15px rgba(19,70,151,.18);
}

.crm-user-box b{
  color:var(--fh-crm-navy)!important;
  font-weight:800!important;
}

.crm-user-box small{
  color:#8795a8!important;
  font-size:9px!important;
}

.crm-logout{
  width:32px;height:32px;
  display:grid;place-items:center;
  border-radius:9px!important;
  color:#7c8ca0!important;
}

.crm-logout:hover{
  background:#fff0f3!important;
  color:var(--fh-crm-red)!important;
}

/* ---------------- GLOBAL TOP BAR ---------------- */
.crm-top{
  height:78px!important;
  padding:0 28px!important;
  background:rgba(255,255,255,.97)!important;
  backdrop-filter:blur(14px);
  border-bottom:1px solid #dfe8f2!important;
  box-shadow:0 5px 18px rgba(11,43,87,.035);
}

.crm-top-left{
  position:relative;
  padding-left:15px;
}

.crm-top-left:before{
  content:"";
  position:absolute;
  left:0;top:2px;bottom:2px;
  width:3px;border-radius:99px;
  background:var(--fh-crm-red);
}

.crm-top-left b{
  display:block;
  color:var(--fh-crm-navy)!important;
  font-size:15px!important;
  font-weight:800!important;
}

.crm-top-left small{
  margin-top:3px;
  color:#8493a7!important;
  font-size:10px!important;
}

.crm-top-actions>.text-muted{
  color:#62748c!important;
  font-size:10px!important;
}

.crm-date-tabs{
  padding:4px!important;
  border:1px solid #e1e8f1;
  background:#f4f7fb!important;
  border-radius:12px!important;
}

.crm-date-tabs span{
  padding:8px 11px!important;
  color:#728198!important;
  font-size:10px!important;
  cursor:default;
}

.crm-date-tabs .active{
  color:var(--fh-crm-navy)!important;
  font-weight:800!important;
  background:#fff!important;
  box-shadow:0 4px 12px rgba(13,41,80,.07)!important;
}

/* ---------------- GLOBAL CONTENT / CARDS ---------------- */
.crm-content{
  padding:24px 28px 30px!important;
  max-width:1720px!important;
}

.crm-card,.card{
  border-color:#dbe5f0!important;
  box-shadow:var(--fh-crm-shadow)!important;
}

.crm-card-head h3{
  color:var(--fh-crm-navy)!important;
  font-weight:800!important;
}

.title,.crm-headline h1,.crm-page-head h1{
  color:var(--fh-crm-navy)!important;
  font-weight:800!important;
}

.btn-primary{
  background:linear-gradient(135deg,#164fbe,#2c6ce8)!important;
  border-color:#205fd4!important;
  box-shadow:0 7px 16px rgba(31,91,216,.15);
}

.btn-danger{
  background:linear-gradient(135deg,var(--fh-crm-red-dark),var(--fh-crm-red))!important;
  border-color:var(--fh-crm-red)!important;
}

/* make generic blue values / links align with the FaresHunt palette */
a{transition:.16s ease}
.booking-link,.crm-card-head a{color:#1858c7!important}
.money{color:#1858c7!important}

/* safer global branding for module titles */
.crm-page-head:before,
.crm-headline:before{
  content:"";
  display:block;
  width:28px;
  height:3px;
  margin:4px 0 9px;
  border-radius:99px;
  background:var(--fh-crm-red);
}

/* modal/toast also follow brand */
.crm-ua-toast{
  background:var(--fh-crm-navy)!important;
}
.crm-ua-btn:hover{
  color:#1858c7!important;
  border-color:#bfd4f4!important;
}
.crm-ua-btn.danger:hover{
  color:var(--fh-crm-red-dark)!important;
}

/* responsive */
@media(max-width:1100px){
  .crm-sidebar{width:235px!important}
  .crm-main{margin-left:235px!important}
  .fh-crm-brand-logo{width:94px!important}
}
@media(max-width:760px){
  .crm-sidebar{transform:translateX(-100%)!important}
  .crm-main{margin-left:0!important}
  .crm-top{padding:0 16px!important}
  .crm-content{padding:18px!important}
}
</style>


<style id="fareshunt-crm-inter-font-v2">
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap");

/* =========================================================
   FaresHunt CRM typography system
   Matches the public website's Inter typography system.
   Layout, routes, permissions and backend logic untouched.
   ========================================================= */

html,
body,
button,
input,
select,
textarea,
table,
th,
td,
a,
span,
div,
p,
small,
strong,
b,
label{
    font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Arial,sans-serif!important;
}

body{
    font-size:15px!important;
    font-weight:400!important;
    line-height:1.5!important;
}

/* Sidebar */
.crm-nav-label{
    font-size:10px!important;
    font-weight:700!important;
}
.crm-link{
    font-size:14px!important;
    font-weight:600!important;
}
.crm-link i{
    font-size:15px!important;
}
.crm-org.fh-crm-org b{
    font-size:14px!important;
}
.crm-org.fh-crm-org small{
    font-size:11px!important;
}
.crm-user-box b{
    font-size:14px!important;
}
.crm-user-box small{
    font-size:11px!important;
}

/* Top bar */
.crm-top-left b{
    font-size:17px!important;
}
.crm-top-left small{
    font-size:12px!important;
}
.crm-top-actions>.text-muted{
    font-size:12px!important;
}
.crm-date-tabs span{
    font-size:12px!important;
}

/* Headings */
.title,
.crm-headline h1,
.crm-page-head h1{
    font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Arial,sans-serif!important;
    font-weight:700!important;
}
.title{
    font-size:32px!important;
}
.sub,
.crm-headline p{
    font-size:14px!important;
    line-height:1.6!important;
}

/* Cards */
.crm-card-head h3,
.card .head h3{
    font-size:16px!important;
    font-weight:700!important;
}
.crm-card-body{
    font-size:14px!important;
}

/* Forms */
.search,
.form-control,
.form-select{
    font-size:14px!important;
}
.btn{
    font-size:13px!important;
    font-weight:600!important;
}
label,
.form-label{
    font-size:13px!important;
}

/* Tables across CRM */
.crm-table th,
.table th{
    font-size:11px!important;
    font-weight:700!important;
    letter-spacing:.035em!important;
}
.crm-table td,
.table td{
    font-size:13px!important;
    line-height:1.45!important;
}
.crm-table td small,
.table td small{
    font-size:11px!important;
    line-height:1.35!important;
}
.booking-link{
    font-size:13px!important;
}
.soft,
.crm-status-badge{
    font-size:11px!important;
}

/* Booking Management page-specific override */
.bm-page .bm-table thead th{
    font-size:11px!important;
}
.bm-page .bm-table tbody td{
    font-size:13px!important;
}
.bm-page .bm-table tbody td small{
    font-size:11px!important;
}
.bm-page .booking-link{
    font-size:13px!important;
}
.bm-page .bm-label{
    font-size:10px!important;
}
.bm-page .bm-search input,
.bm-page .bm-filter-form .form-select,
.bm-page .bm-filter-form .form-control{
    font-size:14px!important;
}
.bm-page .bm-count{
    font-size:11px!important;
}
.bm-page .bm-state{
    font-size:11px!important;
}
.bm-page .bm-pagination{
    font-size:12px!important;
}

/* Detail screens / utility text */
.detail-item span,
.json-row span,
.eyebrow{
    font-size:10px!important;
}
.detail-item strong,
.json-row strong{
    font-size:13px!important;
}
.safe-note,
.crm-back{
    font-size:12px!important;
}

/* KPI / dashboard typography */
.crm-kpi .label,
.crm-smallmetric .label,
.crm-metric .label{
    font-size:11px!important;
}
.crm-kpi .trend,
.crm-smallmetric .delta{
    font-size:12px!important;
}

/* Keep compact helper UI readable */
.crm-ua-btn{
    font-size:11px!important;
}
.crm-ua-form-tools button{
    font-size:10px!important;
}
.crm-ua-protected{
    font-size:10px!important;
}

/* Responsive */
@media(max-width:1100px){
    .crm-link{font-size:13px!important}
    .crm-table td,.table td{font-size:12px!important}
}
@media(max-width:760px){
    body{font-size:14px!important}
    .title{font-size:28px!important}
}
</style>


<style id="fareshunt-crm-premium-v6">
:root{--v6-navy:#081d42;--v6-navy2:#0d2d63;--v6-blue:#1f63e9;--v6-red:#e51f4b;--v6-bg:#f5f8fc;--v6-line:#e0e8f2;--v6-muted:#74849a;--v6-sidebar:246px;--v6-sidebar-collapsed:76px}
html,body{background:var(--v6-bg)!important;color:#0c1d39!important;font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Arial,sans-serif!important}
.crm-sidebar{width:var(--v6-sidebar)!important;background:linear-gradient(180deg,#0a214b 0%,#071a3c 100%)!important;border-right:0!important;box-shadow:12px 0 36px rgba(4,20,48,.09)!important;color:#fff!important;transition:width .22s ease,transform .22s ease!important}
.crm-main{margin-left:var(--v6-sidebar)!important;transition:margin-left .22s ease!important}
.crm-brand.fh-crm-brand{height:68px!important;background:transparent!important;border-bottom:1px solid rgba(255,255,255,.09)!important;padding:0 16px!important}
.fh-crm-brand-logo{width:116px!important;filter:brightness(0) invert(1)!important;opacity:.98}
.fh-crm-brand-copy{border-left-color:rgba(255,255,255,.18)!important}.fh-crm-brand-copy strong{color:#fff!important}.fh-crm-brand-copy small{color:#ff5475!important}
.crm-org.fh-crm-org{margin:11px!important;background:rgba(255,255,255,.07)!important;border:1px solid rgba(255,255,255,.09)!important}.fh-crm-org-icon{background:rgba(255,255,255,.11)!important;box-shadow:none!important}.crm-org.fh-crm-org b{color:#fff!important}.crm-org.fh-crm-org small{color:#a9b8ce!important}.fh-crm-live{color:#7de2a9!important}
.crm-nav{padding:5px 9px 12px!important}.crm-nav-label{color:#7185a4!important;padding:15px 10px 6px!important}.crm-link{color:#c3cee0!important;font-size:12px!important;min-height:38px!important;padding:8px 10px!important;border-radius:9px!important;gap:11px!important}.crm-link i{color:#98aac4!important}.crm-link:hover{background:rgba(255,255,255,.075)!important;color:#fff!important;border-color:transparent!important;transform:none!important}.crm-link.active{background:linear-gradient(90deg,rgba(49,105,225,.32),rgba(40,88,191,.18))!important;border:1px solid rgba(104,151,255,.26)!important;color:#fff!important}.crm-link.active:before{left:-9px!important;background:#ff4167!important}.crm-link.active i{color:#fff!important}
.crm-user{background:rgba(0,0,0,.08)!important;border-top-color:rgba(255,255,255,.08)!important}.crm-user-box b{color:#fff!important}.crm-user-box small{color:#9eafc8!important}.crm-logout{color:#aebbd0!important}.avatar{background:linear-gradient(145deg,#2765e8,#1648ae)!important}
.crm-top{height:68px!important;padding:0 24px!important;background:rgba(255,255,255,.96)!important;border-bottom:1px solid var(--v6-line)!important;box-shadow:0 5px 18px rgba(11,38,78,.03)!important}.crm-top-start,.crm-top-actions{display:flex;align-items:center;gap:12px}.crm-sidebar-toggle{width:38px;height:38px;border:1px solid #dce5ef;border-radius:10px;background:#fff;color:#153058;display:grid;place-items:center;cursor:pointer}.crm-sidebar-toggle:hover{background:#edf4ff;color:var(--v6-blue)}
.crm-global-search{width:min(430px,42vw);height:40px;border:1px solid #dce5ef;border-radius:11px;background:#f9fbfd;display:flex;align-items:center;gap:9px;padding:0 12px}.crm-global-search i{color:#71839a}.crm-global-search input{border:0;outline:0;background:transparent;width:100%;font-size:12px;color:#19304f}.crm-global-search span{font-size:9px;color:#8795a8;background:#fff;border:1px solid #e1e7ef;border-radius:6px;padding:3px 6px;white-space:nowrap}.crm-updated{font-size:10px;color:#7d8ca1}.crm-date-tabs{border:1px solid #e1e8f1!important}.crm-date-tabs a{padding:7px 10px;color:#718099;text-decoration:none;font-size:10px;border-radius:8px}.crm-date-tabs a.active{background:#fff;color:#142a49;font-weight:800;box-shadow:0 2px 8px rgba(12,26,45,.06)}.crm-top-icon{width:36px;height:36px;border:1px solid #dfe6ef;border-radius:10px;display:grid;place-items:center;color:#4c607d;text-decoration:none;background:#fff}
.crm-content{padding:22px 24px 30px!important;max-width:1800px!important}.crm-card,.card{border:1px solid var(--v6-line)!important;border-radius:14px!important;box-shadow:0 10px 28px rgba(11,37,75,.045)!important}.title{font-size:28px!important}.crm-table th,.table th{font-size:9.5px!important;white-space:nowrap}.crm-table td,.table td{font-size:12px!important}.crm-table td small,.table td small{font-size:10px!important}
/* collapsed desktop */
body.crm-sidebar-collapsed .crm-sidebar{width:var(--v6-sidebar-collapsed)!important}body.crm-sidebar-collapsed .crm-main{margin-left:var(--v6-sidebar-collapsed)!important}body.crm-sidebar-collapsed .fh-crm-brand-logo{width:40px!important;object-fit:cover!important;object-position:left!important}body.crm-sidebar-collapsed .fh-crm-brand-copy,body.crm-sidebar-collapsed .crm-org>div,body.crm-sidebar-collapsed .fh-crm-live,body.crm-sidebar-collapsed .crm-nav-label,body.crm-sidebar-collapsed .crm-link:not(.active)::after,body.crm-sidebar-collapsed .crm-user-box>div:not(.avatar),body.crm-sidebar-collapsed .crm-logout{display:none!important}body.crm-sidebar-collapsed .crm-brand.fh-crm-brand{justify-content:center;padding:0!important}body.crm-sidebar-collapsed .crm-org.fh-crm-org{justify-content:center;padding:9px!important}body.crm-sidebar-collapsed .crm-link{justify-content:center;padding:9px!important;font-size:0!important}body.crm-sidebar-collapsed .crm-link i{width:auto!important;font-size:16px!important}body.crm-sidebar-collapsed .crm-user-box{justify-content:center}
.crm-sidebar-overlay{display:none}
@media(max-width:900px){.crm-sidebar{width:246px!important;transform:translateX(-105%)!important}.crm-main,body.crm-sidebar-collapsed .crm-main{margin-left:0!important}body.crm-mobile-menu-open .crm-sidebar{transform:translateX(0)!important}.crm-sidebar-overlay{position:fixed;inset:0;background:rgba(4,18,42,.38);z-index:45}body.crm-mobile-menu-open .crm-sidebar-overlay{display:block}.crm-global-search{width:min(360px,55vw)}.crm-updated{display:none}}
@media(max-width:620px){.crm-top{padding:0 12px!important}.crm-global-search{display:none}.crm-content{padding:16px!important}.crm-date-tabs{display:none!important}}
</style>

<style id="fareshunt-crm-live-polish-v1">
/* =========================================================
   FaresHunt CRM — live-ready global UI polish
   UI only: routes, permissions, controllers and business logic untouched.
   ========================================================= */
:root{
  --fh-sidebar-expanded:246px;
  --fh-sidebar-collapsed:72px;
  --fh-focus:0 0 0 3px rgba(31,99,233,.14);
}

/* One clean brand in the expanded sidebar. */
.crm-brand.fh-crm-brand{
  height:70px!important;
  padding:0 18px!important;
  justify-content:flex-start!important;
  overflow:hidden!important;
}
.fh-crm-brand-mark{
  display:block;
  width:184px;
  height:46px;
  overflow:hidden;
  flex:0 0 auto;
}
.fh-crm-brand-logo{
  width:184px!important;
  height:46px!important;
  max-width:none!important;
  object-fit:contain!important;
  object-position:left center!important;
  display:block!important;
  filter:brightness(0) invert(1)!important;
}
.fh-crm-brand-copy{display:none!important}

/* Sidebar polish */
.crm-sidebar{width:var(--fh-sidebar-expanded)!important}
.crm-main{margin-left:var(--fh-sidebar-expanded)!important}
.crm-nav{
  scrollbar-width:thin;
  scrollbar-color:rgba(255,255,255,.16) transparent;
}
.crm-nav::-webkit-scrollbar{width:5px}
.crm-nav::-webkit-scrollbar-thumb{background:rgba(255,255,255,.16);border-radius:20px}
.crm-link{position:relative!important}
.crm-link:focus-visible,
.crm-sidebar-toggle:focus-visible,
.crm-top-icon:focus-visible,
.btn:focus-visible,
.form-control:focus-visible,
.form-select:focus-visible{
  outline:0!important;
  box-shadow:var(--fh-focus)!important;
}
.crm-link .count{font-size:9px!important}

/* Cleaner organization block */
.crm-org.fh-crm-org{
  min-height:62px!important;
  padding:11px 12px!important;
}
.fh-crm-org-icon{width:34px!important;height:34px!important;flex-basis:34px!important}

/* Top bar polish */
.crm-top{min-height:68px!important}
.crm-sidebar-toggle,.crm-top-icon{transition:background .16s ease,color .16s ease,border-color .16s ease,transform .16s ease}
.crm-sidebar-toggle:active,.crm-top-icon:active{transform:scale(.97)}
.crm-global-search{transition:border-color .16s ease,box-shadow .16s ease,background .16s ease}
.crm-global-search:focus-within{border-color:#b9cdf6!important;background:#fff!important;box-shadow:var(--fh-focus)}
.crm-global-search input::placeholder{color:#8a98aa}

/* General density/readability polish */
.crm-content{padding-top:24px!important}
.crm-card,.card{overflow:hidden}
.crm-card-head,.card .head{background:linear-gradient(180deg,#fff,#fcfdff)}
.table-responsive,.bm-table-wrap{scrollbar-width:thin;scrollbar-color:#c8d3e2 transparent}
.table-responsive::-webkit-scrollbar,.bm-table-wrap::-webkit-scrollbar{height:7px}
.table-responsive::-webkit-scrollbar-thumb,.bm-table-wrap::-webkit-scrollbar-thumb{background:#c8d3e2;border-radius:20px}
.empty{min-height:110px;display:grid;place-items:center}

/* Remove the universal Copy / Clear clutter globally. */
.crm-ua-form-tools{display:none!important}
.crm-ua-actions .crm-ua-btn[title="Copy"],
.crm-ua-actions .crm-ua-btn[title="Clear field"],
#crmUaClear,
#crmUaToast{display:none!important}

/* Desktop collapsed sidebar: compact, centered and intentionally branded. */
body.crm-sidebar-collapsed .crm-sidebar{width:var(--fh-sidebar-collapsed)!important}
body.crm-sidebar-collapsed .crm-main{margin-left:var(--fh-sidebar-collapsed)!important}
body.crm-sidebar-collapsed .crm-brand.fh-crm-brand{
  height:70px!important;
  padding:0!important;
  justify-content:center!important;
}
body.crm-sidebar-collapsed .fh-crm-brand-mark{
  width:42px!important;
  height:44px!important;
  overflow:hidden!important;
  border-radius:8px;
}
body.crm-sidebar-collapsed .fh-crm-brand-logo{
  width:184px!important;
  height:44px!important;
  max-width:none!important;
  object-fit:contain!important;
  object-position:left center!important;
  transform:translateX(0)!important;
}
body.crm-sidebar-collapsed .crm-org.fh-crm-org{display:none!important}
body.crm-sidebar-collapsed .crm-nav{padding:9px 8px 12px!important}
body.crm-sidebar-collapsed .crm-nav-label{display:none!important}
body.crm-sidebar-collapsed .crm-link{
  width:48px!important;
  min-height:44px!important;
  margin:5px auto!important;
  padding:0!important;
  justify-content:center!important;
  gap:0!important;
  font-size:0!important;
  border-radius:11px!important;
}
body.crm-sidebar-collapsed .crm-link i{
  width:20px!important;
  margin:0!important;
  font-size:16px!important;
  text-align:center!important;
}
body.crm-sidebar-collapsed .crm-link .count{
  position:absolute;
  top:3px;
  right:2px;
  min-width:16px;
  height:16px;
  padding:0 4px;
  font-size:8px!important;
}
body.crm-sidebar-collapsed .crm-link.active:before{
  left:-8px!important;
  top:9px!important;
  bottom:9px!important;
  width:3px!important;
}
body.crm-sidebar-collapsed .crm-user{padding:10px 8px!important}
body.crm-sidebar-collapsed .crm-user-box{justify-content:center!important}
body.crm-sidebar-collapsed .avatar{width:36px!important;height:36px!important;flex:0 0 36px!important}
body.crm-sidebar-collapsed .crm-user-box>div:not(.avatar),
body.crm-sidebar-collapsed .crm-user form,
body.crm-sidebar-collapsed .crm-logout{display:none!important}

/* Custom tooltip only in collapsed desktop state. */
body.crm-sidebar-collapsed .crm-link[data-tooltip]:after{
  content:attr(data-tooltip)!important;
  display:block!important;
  position:absolute;
  left:58px;
  top:50%;
  transform:translateY(-50%) translateX(-4px);
  padding:7px 9px;
  border-radius:8px;
  background:#0b1f43;
  color:#fff;
  font-size:11px!important;
  font-weight:650;
  line-height:1;
  white-space:nowrap;
  opacity:0;
  visibility:hidden;
  pointer-events:none;
  box-shadow:0 10px 26px rgba(3,17,42,.2);
  transition:.14s ease;
  z-index:120;
}
body.crm-sidebar-collapsed .crm-link[data-tooltip]:hover:after,
body.crm-sidebar-collapsed .crm-link[data-tooltip]:focus-visible:after{
  opacity:1;
  visibility:visible;
  transform:translateY(-50%) translateX(0);
}

@media(max-width:900px){
  .crm-sidebar{width:246px!important}
  .crm-main,body.crm-sidebar-collapsed .crm-main{margin-left:0!important}
  body.crm-sidebar-collapsed .crm-sidebar{width:246px!important}
  body.crm-sidebar-collapsed .crm-org.fh-crm-org{display:flex!important}
  body.crm-sidebar-collapsed .crm-nav-label{display:block!important}
  body.crm-sidebar-collapsed .crm-link{
    width:auto!important;min-height:38px!important;margin:2px 0!important;padding:8px 10px!important;
    justify-content:flex-start!important;gap:11px!important;font-size:12px!important;
  }
  body.crm-sidebar-collapsed .crm-link i{width:21px!important;font-size:14px!important}
  body.crm-sidebar-collapsed .crm-link[data-tooltip]:after{display:none!important}
  body.crm-sidebar-collapsed .crm-user{padding:12px 14px!important}
  body.crm-sidebar-collapsed .crm-user-box{justify-content:flex-start!important}
  body.crm-sidebar-collapsed .crm-user-box>div:not(.avatar),body.crm-sidebar-collapsed .crm-user form{display:block!important}
}
@media(max-width:620px){
  .crm-brand.fh-crm-brand{padding-left:14px!important}
  .fh-crm-brand-mark{width:168px;height:42px}
  .fh-crm-brand-logo{width:168px!important;height:42px!important}
}
</style>


<style id="fareshunt-crm-logo-final-v2">
/* Final logo geometry: full expanded logo, clean icon crop only when collapsed. */
.crm-brand.fh-crm-brand{
  min-height:74px!important;
  height:74px!important;
  overflow:visible!important;
  padding:0 16px!important;
}
.fh-crm-brand-mark{
  width:186px!important;
  height:56px!important;
  overflow:visible!important;
  display:flex!important;
  align-items:center!important;
  justify-content:flex-start!important;
}
.fh-crm-brand-logo{
  display:block!important;
  width:180px!important;
  height:auto!important;
  max-width:180px!important;
  max-height:54px!important;
  object-fit:contain!important;
  object-position:left center!important;
  transform:none!important;
}
body.crm-sidebar-collapsed .crm-brand.fh-crm-brand{height:70px!important;min-height:70px!important;overflow:hidden!important;padding:0!important}
body.crm-sidebar-collapsed .fh-crm-brand-mark{width:44px!important;height:44px!important;overflow:hidden!important;display:block!important}
body.crm-sidebar-collapsed .fh-crm-brand-logo{width:180px!important;max-width:none!important;height:44px!important;max-height:44px!important;object-fit:contain!important;object-position:left center!important}
@media(max-width:900px){
  .crm-brand.fh-crm-brand{height:72px!important;min-height:72px!important}
  .fh-crm-brand-mark{width:178px!important;height:52px!important}
  .fh-crm-brand-logo{width:174px!important;max-width:174px!important;max-height:50px!important}
}
</style>


<style id="fareshunt-crm-global-production-normalize-v4">
/* =========================================================
   FaresHunt CRM — Global Production Typography + Alignment
   UI only. Routes, permissions, controllers and business
   logic are intentionally untouched.
   ========================================================= */

:root{
    --fh-crm-font:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Arial,sans-serif;
}

/* ---------- ONE FONT ACROSS THE ENTIRE CRM ---------- */
html,
body,
.crm-shell,
.crm-sidebar,
.crm-main,
.crm-top,
.crm-content,
.crm-content *,
button,
input,
select,
textarea,
option,
table,
thead,
tbody,
tfoot,
tr,
th,
td,
a,
span,
div,
p,
small,
strong,
b,
label,
h1,
h2,
h3,
h4,
h5,
h6,
.btn,
.form-control,
.form-select,
.dropdown-menu,
.modal,
.tooltip,
.popover{
    font-family:var(--fh-crm-font)!important;
}

/* Font Awesome must retain its own icon fonts. */
.fa,.fas,.far,.fal,.fat,.fad,.fab,
.fa-solid,.fa-regular,.fa-light,.fa-thin,.fa-duotone,.fa-brands,
[class^="fa-"],[class*=" fa-"]{
    font-family:"Font Awesome 6 Free","Font Awesome 6 Pro","Font Awesome 5 Free","Font Awesome 5 Pro",FontAwesome!important;
}
.fa-brands,.fab{
    font-family:"Font Awesome 6 Brands","Font Awesome 5 Brands"!important;
}

input::placeholder,
textarea::placeholder{
    font-family:var(--fh-crm-font)!important;
}

/* ---------- MAIN SHELL / CONTENT ALIGNMENT ---------- */
.crm-main{
    width:auto!important;
    min-width:0!important;
}

.crm-content{
    width:100%!important;
    max-width:1800px!important;
    margin-left:auto!important;
    margin-right:auto!important;
    box-sizing:border-box!important;
    overflow-x:hidden!important;
}

.crm-content > *,
.crm-content > div,
.crm-content > section,
.crm-content > main{
    max-width:100%!important;
    box-sizing:border-box!important;
}

.crm-content .container,
.crm-content .container-fluid{
    width:100%!important;
    max-width:100%!important;
    margin-left:0!important;
    margin-right:0!important;
}

.crm-content [class$="-page"],
.crm-content [class*="-page "],
.crm-content [class$="-wrapper"],
.crm-content [class*="-wrapper "]{
    max-width:100%!important;
    box-sizing:border-box!important;
}

/* Allow data tables to scroll without pushing the whole page. */
.crm-content .table-responsive,
.crm-content .bm-table-wrap{
    max-width:100%!important;
    overflow-x:auto!important;
}

/* ---------- PAGE HEADERS ---------- */
.crm-page-head,
.crm-headline{
    width:100%!important;
    max-width:none!important;
    margin-left:0!important;
    margin-right:0!important;
    display:flex!important;
    align-items:flex-start!important;
    justify-content:space-between!important;
    gap:20px!important;
}

.crm-page-head > div:first-child,
.crm-headline > div:first-child{
    min-width:0!important;
}

/* Old decorative pseudo element was creating visible offset on some pages. */
.crm-page-head:before,
.crm-headline:before{
    content:none!important;
    display:none!important;
}

/* Main headings: clean medium weight, not heavy bold. */
.crm-content h1,
.crm-content .title,
.crm-content .crm-headline h1,
.crm-content .crm-page-head h1{
    font-weight:500!important;
    letter-spacing:-.025em!important;
}

/* Section headings throughout CRM. */
.crm-content h2,
.crm-content h3,
.crm-content h4,
.crm-content h5,
.crm-content h6,
.crm-content .crm-card-head h3,
.crm-content .card .head h3,
.crm-content .card-title,
.crm-content .section-title,
.crm-content .panel-title{
    font-weight:500!important;
}

/* Normal content should remain regular. */
.crm-content p,
.crm-content td,
.crm-content .form-control,
.crm-content .form-select,
.crm-content .search{
    font-weight:400!important;
}

/* Tables: labels readable but not excessively bold. */
.crm-table th,
.table th{
    font-weight:600!important;
}
.crm-table td,
.table td{
    font-weight:400!important;
}

/* Buttons / nav use medium weight. */
.btn,
button{
    font-weight:500!important;
}
.crm-link{
    font-weight:500!important;
}
.crm-nav-label{
    font-weight:600!important;
}
.crm-user-box b,
.crm-org b{
    font-weight:600!important;
}

/* ---------- CARDS / KPI / GRID NORMALIZATION ---------- */
.crm-card,
.card,
.crm-kpis,
.crm-grid4,
.crm-kpi-grid,
.crm-stat-strip,
.crm-metric-grid,
.crm-lower,
.crm-chart-wrap,
.crm-filterbar{
    width:100%!important;
    max-width:100%!important;
    box-sizing:border-box!important;
}

.crm-kpis > *,
.crm-grid4 > *,
.crm-kpi-grid > *,
.crm-stat-strip > *,
.crm-metric-grid > *,
.crm-lower > *,
.crm-chart-wrap > *{
    min-width:0!important;
}

/* ---------- FILTER / SEARCH BARS ---------- */
.crm-filterbar{
    align-items:center!important;
}

.crm-filterbar .search,
.crm-filterbar input[type="search"],
.crm-filterbar input[type="text"]{
    min-width:0!important;
}

/* ---------- GENERIC TABLE WIDTH ---------- */
.crm-table,
.table{
    width:100%!important;
    max-width:100%!important;
}

/* ---------- UNIVERSAL ACTION CLUTTER ---------- */
.crm-ua-form-tools{
    display:none!important;
}
.crm-ua-actions .crm-ua-btn[title="Copy"],
.crm-ua-actions .crm-ua-btn[title="Clear field"],
#crmUaClear,
#crmUaToast{
    display:none!important;
}

/* ---------- RESPONSIVE ---------- */
@media(max-width:1100px){
    .crm-content{
        width:100%!important;
    }

    .crm-page-head,
    .crm-headline{
        gap:14px!important;
    }
}

@media(max-width:900px){
    .crm-main,
    body.crm-sidebar-collapsed .crm-main{
        margin-left:0!important;
    }
}

@media(max-width:760px){
    .crm-content{
        width:100%!important;
        padding-left:16px!important;
        padding-right:16px!important;
    }

    .crm-page-head,
    .crm-headline{
        flex-direction:column!important;
        align-items:flex-start!important;
    }

    .crm-page-head > *,
    .crm-headline > *{
        width:100%!important;
    }

    .crm-content h1,
    .crm-content .title,
    .crm-content .crm-headline h1,
    .crm-content .crm-page-head h1{
        font-size:27px!important;
    }
}
</style>


<style id="fareshunt-crm-global-title-system-v5">
/* =========================================================
   FaresHunt CRM — one title system across every CRM module
   Pattern:
   red line + blue uppercase eyebrow
   large clean regular/medium title
   optional subtitle below
   ========================================================= */

.fh-standard-title-block{
    position:relative!important;
    display:block!important;
    padding-top:0!important;
    min-width:0!important;
}

/* Hide only OLD page-level eyebrow/badge elements sitting directly
   beside the first H1. Card-level kickers elsewhere are untouched. */
.fh-standard-title-block > .lm-eyebrow,
.fh-standard-title-block > .rp-pill,
.fh-standard-title-block > .pm-eyebrow,
.fh-standard-title-block > .cm-eyebrow,
.fh-standard-title-block > .crm-eyebrow,
.fh-standard-title-block > .eyebrow,
.fh-standard-title-block > [data-page-eyebrow]{
    display:none!important;
}

.fh-page-eyebrow{
    display:flex!important;
    align-items:center!important;
    gap:14px!important;
    width:max-content!important;
    max-width:100%!important;
    margin:0 0 8px!important;
    color:#1554c5!important;
    font-family:var(--fh-crm-font,Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Arial,sans-serif)!important;
    font-size:11px!important;
    line-height:1!important;
    font-weight:700!important;
    letter-spacing:.13em!important;
    text-transform:uppercase!important;
    white-space:nowrap!important;
}

.fh-page-eyebrow:before{
    content:""!important;
    display:block!important;
    width:34px!important;
    height:3px!important;
    flex:0 0 34px!important;
    border-radius:99px!important;
    background:#e3194a!important;
}

.fh-standard-title-block > h1{
    margin:0!important;
    color:#071f4f!important;
    font-family:var(--fh-crm-font,Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Arial,sans-serif)!important;
    font-size:36px!important;
    line-height:1.08!important;
    font-weight:500!important;
    letter-spacing:-.035em!important;
}

/* Keep subtitles consistent without forcing pages that do not have one. */
.fh-standard-title-block > h1 + p,
.fh-standard-title-block > h1 + .sub,
.fh-standard-title-block > h1 + small{
    margin-top:7px!important;
    margin-bottom:0!important;
    color:#718099!important;
    font-size:14px!important;
    line-height:1.55!important;
    font-weight:400!important;
}

/* Page header wrappers keep actions on the right. */
.crm-content .lm-page-head,
.crm-content .rp-head,
.crm-content .crm-page-head,
.crm-content .crm-headline,
.crm-content .pm-page-head,
.crm-content .cm-page-head{
    align-items:flex-start!important;
}

/* Prevent older global pseudo-lines from doubling the red line. */
.crm-content .crm-page-head:before,
.crm-content .crm-headline:before{
    content:none!important;
    display:none!important;
}

@media(max-width:900px){
    .fh-standard-title-block > h1{
        font-size:32px!important;
    }
}

@media(max-width:620px){
    .fh-page-eyebrow{
        gap:10px!important;
        font-size:10px!important;
        letter-spacing:.10em!important;
        white-space:normal!important;
    }
    .fh-page-eyebrow:before{
        width:28px!important;
        flex-basis:28px!important;
    }
    .fh-standard-title-block > h1{
        font-size:29px!important;
    }
}
</style>



<style id="fareshunt-crm-collapsed-sidebar-final-v8">
/* Final desktop collapsed-sidebar polish. UI only. */
@media (min-width:901px){
  .crm-sidebar{overflow:hidden!important;}
  .crm-nav{min-height:0!important;flex:1 1 auto!important;overflow-y:auto!important;overflow-x:hidden!important;}

  body.crm-sidebar-collapsed .crm-sidebar{width:72px!important;overflow:hidden!important;}
  body.crm-sidebar-collapsed .crm-main{margin-left:72px!important;}
  body.crm-sidebar-collapsed .crm-brand.fh-crm-brand{height:70px!important;min-height:70px!important;flex:0 0 70px!important;}
  body.crm-sidebar-collapsed .fh-crm-brand-mark{width:46px!important;height:46px!important;border-radius:10px!important;}
  body.crm-sidebar-collapsed .fh-crm-brand-logo{width:180px!important;height:46px!important;max-width:none!important;max-height:46px!important;}
  body.crm-sidebar-collapsed .crm-org.fh-crm-org{display:none!important;}

  body.crm-sidebar-collapsed .crm-nav{
    padding:8px 7px 10px!important;
    overflow-y:auto!important;
    overflow-x:hidden!important;
    scrollbar-width:none!important;
    -ms-overflow-style:none!important;
  }
  body.crm-sidebar-collapsed .crm-nav::-webkit-scrollbar{width:0!important;height:0!important;display:none!important;}
  body.crm-sidebar-collapsed .crm-nav-label{display:none!important;}
  body.crm-sidebar-collapsed .crm-link{
    width:50px!important;
    min-width:50px!important;
    max-width:50px!important;
    height:46px!important;
    min-height:46px!important;
    margin:4px auto!important;
    padding:0!important;
    display:flex!important;
    align-items:center!important;
    justify-content:center!important;
    overflow:hidden!important;
  }
  body.crm-sidebar-collapsed .crm-link i{width:22px!important;min-width:22px!important;margin:0!important;font-size:17px!important;text-align:center!important;}
  body.crm-sidebar-collapsed .crm-link.active:before{left:-7px!important;top:8px!important;bottom:8px!important;}
  body.crm-sidebar-collapsed .crm-link .count{top:3px!important;right:3px!important;}

  /* Pseudo tooltips were widening the scrollable nav and creating the bottom horizontal scrollbar. */
  body.crm-sidebar-collapsed .crm-link[data-tooltip]:after{display:none!important;content:none!important;}

  body.crm-sidebar-collapsed .crm-user{flex:0 0 auto!important;padding:10px 7px 12px!important;overflow:hidden!important;}
  body.crm-sidebar-collapsed .crm-user-box{justify-content:center!important;gap:0!important;}
  body.crm-sidebar-collapsed .avatar{width:42px!important;height:42px!important;flex:0 0 42px!important;font-size:14px!important;}
}
</style>
</head>
<body data-crm-ua-field-url="{{ route('crm.universal_field_update') }}">
@php $crmAdmin=\App\Models\Admin::find(session('crm_admin_id')); $crmAccess=app(\App\Services\CrmAccessService::class); $crmAdminId=(int)session('crm_admin_id'); $path=request()->path(); @endphp
<div class="crm-shell">
<aside class="crm-sidebar">
 <div class="crm-brand fh-crm-brand" aria-label="FaresHunt CRM">
        <span class="fh-crm-brand-mark">
            <img src="/assets/img/fareshunt-logo-transparent.png" alt="FaresHunt" class="fh-crm-brand-logo">
        </span>
    </div>
 <div class="crm-org fh-crm-org">
        <span class="fh-crm-org-icon"><i class="fa-solid fa-plane"></i></span>
        <div>
            <b>FaresHunt</b>
            <small>Operations Workspace</small>
        </div>
        <span class="fh-crm-live"><i class="fa-solid fa-circle"></i> Live</span>
    </div>
 <nav class="crm-nav">
  <div class="crm-nav-label">Operations</div>
  @if($crmAccess->can($crmAdminId,'dashboard.view'))<a class="crm-link {{ request()->routeIs('crm.dashboard')?'active':'' }}" href="{{ route('crm.dashboard') }}"><i class="fa-solid fa-table-cells-large"></i>Smart Dashboard</a>@endif
  @if($crmAccess->can($crmAdminId,'bookings.view'))<a class="crm-link {{ request()->routeIs('crm.bookings','crm.booking_detail')?'active':'' }}" href="{{ route('crm.bookings') }}"><i class="fa-regular fa-book-open"></i>Booking Management</a>@endif
  @if($crmAccess->can($crmAdminId,'bookings.view'))<a class="crm-link {{ request()->routeIs('crm.incomplete_bookings','crm.incomplete_booking_detail')?'active':'' }}" href="{{ route('crm.incomplete_bookings') }}"><i class="fa-solid fa-clock-rotate-left"></i>Incomplete Bookings</a>@endif
  @if($crmAccess->can($crmAdminId,'leads.view'))<a class="crm-link {{ request()->routeIs('crm.leads','crm.lead_detail')?'active':'' }}" href="{{ route('crm.leads') }}"><i class="fa-solid fa-user-plus"></i>Lead Management</a>@endif
  @if($crmAccess->can($crmAdminId,'customers.view'))<a class="crm-link {{ request()->routeIs('crm.customers','crm.customer_detail')?'active':'' }}" href="{{ route('crm.customers') }}"><i class="fa-solid fa-users"></i>Customers</a>@endif
  @if($crmAccess->can($crmAdminId,'payments.view'))<a class="crm-link {{ request()->routeIs('crm.payments')?'active':'' }}" href="{{ route('crm.payments') }}"><i class="fa-regular fa-credit-card"></i>Payments</a>@endif
  @if($crmAccess->can($crmAdminId,'stripe_gateway.view') && \Illuminate\Support\Facades\Route::has('crm.stripe_gateway'))<a class="crm-link {{ request()->routeIs('crm.stripe_gateway*')?'active':'' }}" href="{{ route('crm.stripe_gateway') }}"><i class="fa-brands fa-stripe-s"></i>Payment Gateway</a>@endif
  @if($crmAccess->can($crmAdminId,'refunds.view'))<a class="crm-link {{ request()->routeIs('crm.refunds')?'active':'' }}" href="{{ route('crm.refunds') }}"><i class="fa-solid fa-arrow-rotate-left"></i>Refunds & Chargebacks</a>@endif
  @if($crmAccess->can($crmAdminId,'communications.view'))<a class="crm-link {{ request()->routeIs('crm.communications')?'active':'' }}" href="{{ route('crm.communications') }}"><i class="fa-regular fa-message"></i>Communications</a>@endif

  <div class="crm-nav-label">Travel Products</div>
  {{-- One unified Travel Desk entry. Existing type-specific routes remain untouched and can still be used inside the workspace. --}}
  <a class="crm-link {{ request()->routeIs('crm.travel_desk*')?'active':'' }}" href="{{ route('crm.travel_desk') }}"><i class="fa-solid fa-layer-group"></i>Travel Desk</a>
  @if($crmAccess->can($crmAdminId,'travel_offers.view'))<a class="crm-link {{ request()->routeIs('crm.travel_offers*')?'active':'' }}" href="{{ route('crm.travel_offers') }}"><i class="fa-solid fa-tags"></i>Travel Offers</a>@endif
  @if($crmAccess->can($crmAdminId,'travel_plans.view'))<a class="crm-link {{ request()->routeIs('crm.travel_plans*')?'active':'' }}" href="{{ route('crm.travel_plans') }}"><i class="fa-solid fa-suitcase-rolling"></i>Travel Plans</a>@endif
  @if($crmAccess->can($crmAdminId,'master_data.view'))<a class="crm-link {{ request()->routeIs('crm.master_data*')?'active':'' }}" href="{{ route('crm.master_data') }}"><i class="fa-solid fa-globe"></i>Travel Master Data</a>@endif

  <div class="crm-nav-label">Configuration</div>
  @if($crmAccess->can($crmAdminId,'fare_rules.view'))<a class="crm-link {{ request()->routeIs('crm.fare_rules','crm.fare_rule_*')?'active':'' }}" href="{{ route('crm.fare_rules') }}"><i class="fa-solid fa-sliders"></i>Fare Rule Engine</a>@endif
  @if($crmAccess->can($crmAdminId,'reports.view'))<a class="crm-link {{ request()->routeIs('crm.reports')?'active':'' }}" href="{{ route('crm.reports') }}"><i class="fa-solid fa-chart-column"></i>Reports & Analytics</a>@endif
  @if($crmAccess->can($crmAdminId,'profit_report.view'))<a class="crm-link {{ request()->routeIs('crm.profit_report*')?'active':'' }}" href="{{ route('crm.profit_report') }}"><i class="fa-solid fa-coins"></i>Profit Report</a>@endif
  @if($crmAccess->can($crmAdminId,'notifications.view'))<a class="crm-link {{ request()->routeIs('crm.notifications')?'active':'' }}" href="{{ route('crm.notifications') }}"><i class="fa-regular fa-bell"></i>Notifications</a>@endif
  @if($crmAccess->can($crmAdminId,'email_templates.view'))<a class="crm-link {{ request()->routeIs('crm.email_templates')?'active':'' }}" href="{{ route('crm.email_templates') }}"><i class="fa-regular fa-envelope"></i>Email Templates</a>@endif
  @if($crmAccess->can($crmAdminId,'website_analytics.view') && \Illuminate\Support\Facades\Route::has('crm.website_analytics'))<a class="crm-link {{ request()->routeIs('crm.website_analytics')?'active':'' }}" href="{{ route('crm.website_analytics') }}"><i class="fa-solid fa-chart-line"></i>Website Analytics</a>@endif
  @if($crmAccess->can($crmAdminId,'google_analytics.view') && \Illuminate\Support\Facades\Route::has('crm.google_analytics'))<a class="crm-link {{ request()->routeIs('crm.google_analytics')?'active':'' }}" href="{{ route('crm.google_analytics') }}"><i class="fa-brands fa-google"></i>Google Analytics</a>@endif
  @if($crmAccess->can($crmAdminId,'meta_monitor.view') && \Illuminate\Support\Facades\Route::has('crm.meta_monitor'))<a class="crm-link {{ request()->routeIs('crm.meta_monitor')?'active':'' }}" href="{{ route('crm.meta_monitor') }}"><i class="fa-solid fa-gauge-high"></i>Meta Monitor</a>@endif
  @if($crmAccess->can($crmAdminId,'live_pax.view') && \Illuminate\Support\Facades\Route::has('crm.live_pax'))<a class="crm-link {{ request()->routeIs('crm.live_pax*')?'active':'' }}" href="{{ route('crm.live_pax') }}" data-tooltip="Live Pax Monitor"><i class="fa-solid fa-users-viewfinder"></i>Live Pax Monitor</a>@endif

  <a class="crm-link {{ request()->routeIs('crm.integrations')?'active':'' }}" href="{{ route('crm.integrations') }}"><i class="fa-solid fa-puzzle-piece"></i>Integration Hub</a>

  <div class="crm-nav-label">System</div>
  @if($crmAccess->can($crmAdminId,'followups.view'))<a class="crm-link {{ request()->routeIs('crm.followups','crm.followups_v2')?'active':'' }}" href="{{ route('crm.followups_v2') }}"><i class="fa-regular fa-circle-check"></i>Follow-ups</a>@endif
  @if($crmAccess->can($crmAdminId,'api_control.view'))<a class="crm-link {{ request()->routeIs('crm.api_control*')?'active':'' }}" href="{{ route('crm.api_control_v3_1') }}"><i class="fa-solid fa-plug"></i>API Control</a>@endif
  @if($crmAccess->can($crmAdminId,'tracking.view'))<a class="crm-link {{ request()->routeIs('crm.tracking*')?'active':'' }}" href="{{ route('crm.tracking_v2') }}"><i class="fa-solid fa-route"></i>Tracking</a>@endif
  @if($crmAccess->can($crmAdminId,'cms.view'))<a class="crm-link {{ request()->routeIs('crm.cms','crm.pages_manager','crm.content_manager','crm.footer_manager')?'active':'' }}" href="{{ route('crm.cms') }}"><i class="fa-regular fa-file-lines"></i>CMS</a>@endif
  @if($crmAccess->can($crmAdminId,'settings.view'))<a class="crm-link {{ request()->routeIs('crm.settings')?'active':'' }}" href="{{ route('crm.settings') }}"><i class="fa-solid fa-gear"></i>Settings</a>@endif
  @if($crmAccess->can($crmAdminId,'users.view'))<a class="crm-link {{ request()->routeIs('crm.team_access')?'active':'' }}" href="{{ route('crm.team_access') }}"><i class="fa-solid fa-user-shield"></i>Team & Access</a>@endif
  @if($crmAccess->can($crmAdminId,'trash.view'))<a class="crm-link {{ request()->routeIs('crm.universal_trash')?'active':'' }}" href="{{ route('crm.universal_trash') }}"><i class="fa-regular fa-trash-can"></i>CRM Trash</a>@endif
 </nav>
 <div class="crm-user"><div class="crm-user-box"><div class="avatar">{{ strtoupper(substr($crmAdmin->name ?? 'A',0,2)) }}</div><div><b>{{ $crmAdmin->name ?? 'Administrator' }}</b><small>{{ $crmAccess->roleName($crmAdminId) }}</small></div><form method="POST" action="{{ route('crm.logout') }}" class="ms-auto">@csrf<button class="crm-logout" title="Sign out"><i class="fa-solid fa-arrow-right-from-bracket"></i></button></form></div></div>
</aside>
<div class="crm-sidebar-overlay" id="crmSidebarOverlay"></div>
<main class="crm-main">
 <header class="crm-top">
  <div class="crm-top-start">
    <button type="button" class="crm-sidebar-toggle" id="crmSidebarToggle" title="Hide / show menu"><i class="fa-solid fa-bars"></i></button>
    <form class="crm-global-search" method="GET" action="{{ route('crm.bookings') }}">
      <i class="fa-solid fa-magnifying-glass"></i><input name="q" placeholder="Search bookings, customers, PNR, tickets..." autocomplete="off"><span>Ctrl /</span>
    </form>
  </div>
  <div class="crm-top-actions">
    <span class="crm-updated">Updated {{ now()->format('h:i A') }}</span>
    @if(request()->routeIs('crm.dashboard'))
      <div class="crm-date-tabs">
        <a href="{{ route('crm.dashboard',['period'=>'1d']) }}" class="{{ request('period','30d')==='1d'?'active':'' }}">24h</a>
        <a href="{{ route('crm.dashboard',['period'=>'7d']) }}" class="{{ request('period','30d')==='7d'?'active':'' }}">7 days</a>
        <a href="{{ route('crm.dashboard',['period'=>'30d']) }}" class="{{ request('period','30d')==='30d'?'active':'' }}">30 days</a>
        <a href="{{ route('crm.dashboard',['period'=>'all']) }}" class="{{ request('period','30d')==='all'?'active':'' }}">All</a>
      </div>
    @endif
    <a class="crm-top-icon" href="{{ route('crm.notifications') }}" title="Notifications"><i class="fa-regular fa-bell"></i></a>
  </div>
</header>
 <div class="crm-content">
 @if(session('crm_success'))<div class="crm-success">{{ session('crm_success') }}</div>@endif
 @if($errors->any() && !request()->routeIs('crm.fare_rules'))<div class="crm-danger">{{ $errors->first() }}</div>@endif
 @yield('content')
 </div>
</main>
</div>

<div class="crm-ua-modal-backdrop" id="crmUaModalBackdrop">
  <div class="crm-ua-modal" role="dialog" aria-modal="true">
    <form method="POST" action="{{ route('crm.universal_field_update') }}" id="crmUaEditForm">
      @csrf
      <input type="hidden" name="module" id="crmUaModule">
      <input type="hidden" name="record_key" id="crmUaRecordKey">
      <input type="hidden" name="field" id="crmUaField">
      <input type="hidden" name="return_to" value="{{ url()->current() }}">
      <div class="crm-ua-modal-head">
        <div><span class="eyebrow">Universal edit</span><h3 id="crmUaTitle">Edit field</h3></div>
        <button type="button" class="crm-ua-btn" id="crmUaClose"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="crm-ua-modal-body">
        <label style="display:block">
          <span style="display:block;font-size:10px;color:#728198;text-transform:uppercase;font-weight:700;margin-bottom:7px">Value</span>
          <textarea class="form-control" rows="4" name="value" id="crmUaValue"></textarea>
        </label>
        <p style="font-size:10px;color:#8593a6;margin:9px 0 0">Protected/system fields cannot be edited from this control.</p>
      </div>
      <div class="crm-ua-modal-actions">
        <button type="button" class="btn btn-light" id="crmUaCancel">Cancel</button>
        <button class="btn btn-primary">Save changes</button>
      </div>
    </form>
  </div>
</div>
<div class="crm-ua-toast" id="crmUaToast">Copied</div>

<script id="crm-universal-actions-js-v2">
(function(){
  const qs=(s,r=document)=>r.querySelector(s), qsa=(s,r=document)=>Array.from(r.querySelectorAll(s));

  // Keep the persisted universal Edit capability, but do not inject Copy/Clear controls.
  const backdrop=qs('#crmUaModalBackdrop'), value=qs('#crmUaValue');
  function close(){ if(backdrop) backdrop.style.display='none'; }
  function openFor(el){
    const module=qs('#crmUaModule'), key=qs('#crmUaRecordKey'), field=qs('#crmUaField'), title=qs('#crmUaTitle');
    if(module) module.value=el.dataset.uaModule||'';
    if(key) key.value=el.dataset.uaKey||'';
    if(field) field.value=el.dataset.uaField||'';
    if(title) title.textContent='Edit '+(el.dataset.uaLabel||el.dataset.uaField||'field');
    if(value) value.value=el.dataset.uaValue??el.querySelector('strong')?.innerText??'';
    if(backdrop) backdrop.style.display='flex';
    setTimeout(()=>value?.focus(),30);
  }

  qsa('[data-crm-ua]').forEach(el=>{
    if(el.dataset.crmEditAdded==='1') return;
    el.dataset.crmEditAdded='1';
    const strong=el.querySelector('strong')||el;
    const actions=document.createElement('span');
    actions.className='crm-ua-actions';
    const edit=document.createElement('button');
    edit.type='button';
    edit.className='crm-ua-btn';
    edit.title='Edit';
    edit.setAttribute('aria-label','Edit');
    edit.innerHTML='<i class="fa-regular fa-pen-to-square"></i>';
    edit.onclick=(e)=>{e.preventDefault();e.stopPropagation();openFor(el);};
    actions.appendChild(edit);
    strong.insertAdjacentElement('afterend',actions);
  });

  qs('#crmUaClose')?.addEventListener('click',close);
  qs('#crmUaCancel')?.addEventListener('click',close);
  backdrop?.addEventListener('click',e=>{if(e.target===backdrop)close();});
})();
</script>


<script id="fareshunt-sidebar-v7">
(function(){
  const body=document.body, btn=document.getElementById('crmSidebarToggle'), overlay=document.getElementById('crmSidebarOverlay');
  const key='fareshunt.crm.sidebar.collapsed';
  const mobile=()=>window.matchMedia('(max-width:900px)').matches;

  // Build labels once; CSS shows them as tooltips only while collapsed.
  document.querySelectorAll('.crm-link').forEach(link=>{
    const label=(link.textContent||'').replace(/\s+/g,' ').trim();
    if(label){ link.dataset.tooltip=label; link.setAttribute('aria-label',label); link.setAttribute('title',label); }
  });

  if(!mobile() && localStorage.getItem(key)==='1') body.classList.add('crm-sidebar-collapsed');

  function setExpandedAria(){
    btn?.setAttribute('aria-expanded', mobile() ? String(body.classList.contains('crm-mobile-menu-open')) : String(!body.classList.contains('crm-sidebar-collapsed')));
  }
  function toggle(){
    if(mobile()){
      body.classList.toggle('crm-mobile-menu-open');
      setExpandedAria();
      return;
    }
    body.classList.toggle('crm-sidebar-collapsed');
    localStorage.setItem(key,body.classList.contains('crm-sidebar-collapsed')?'1':'0');
    setExpandedAria();
  }

  btn?.addEventListener('click',toggle);
  overlay?.addEventListener('click',()=>{body.classList.remove('crm-mobile-menu-open');setExpandedAria();});
  window.addEventListener('resize',()=>{
    if(!mobile()) body.classList.remove('crm-mobile-menu-open');
    setExpandedAria();
  },{passive:true});
  document.addEventListener('keydown',e=>{
    if(e.key==='Escape' && body.classList.contains('crm-mobile-menu-open')){body.classList.remove('crm-mobile-menu-open');setExpandedAria();}
    if((e.ctrlKey||e.metaKey)&&e.key==='/'){e.preventDefault();document.querySelector('.crm-global-search input')?.focus();}
  });
  setExpandedAria();
})();
</script>


<script id="fareshunt-crm-global-title-system-v5-js">
document.addEventListener('DOMContentLoaded', function(){
    const content = document.querySelector('.crm-content');
    if(!content) return;

    const h1 = content.querySelector('h1');
    if(!h1 || h1.dataset.fhTitleStandardized === '1') return;

    const routeName = @json(optional(request()->route())->getName() ?? '');

    function eyebrowFor(route){
        route = String(route || '');

        if(route === 'crm.dashboard') return 'Operations Overview';

        if(route.startsWith('crm.booking') ||
           route.startsWith('crm.manual_booking') ||
           route.startsWith('crm.invoice')) return 'Booking Operations';

        if(route.startsWith('crm.lead')) return 'Sales & Enquiries';
        if(route.startsWith('crm.customer')) return 'Customer Operations';
        if(route.startsWith('crm.payment')) return 'Payment Operations';
        if(route.startsWith('crm.communication')) return 'Customer Communications';

        if(route.startsWith('crm.travel_desk')) return 'Travel Operations';
        if(route.startsWith('crm.fare_rule')) return 'Pricing & Fare Control';
        if(route.startsWith('crm.report')) return 'Operations Intelligence';
        if(route.startsWith('crm.notification')) return 'Operations Alerts';
        if(route.startsWith('crm.email_template')) return 'Customer Messaging';
        if(route.startsWith('crm.website_analytics')) return 'Website Intelligence';
        if(route.startsWith('crm.integration')) return 'Integration Operations';

        if(route.startsWith('crm.followup')) return 'Follow-up Operations';
        if(route.startsWith('crm.api_control')) return 'API Operations';
        if(route.startsWith('crm.tracking')) return 'Booking Tracking';

        if(route.startsWith('crm.cms') ||
           route.startsWith('crm.pages_manager') ||
           route.startsWith('crm.content_manager') ||
           route.startsWith('crm.footer_manager')) return 'Website Content';

        if(route.startsWith('crm.setting')) return 'System Settings';
        if(route.includes('team') || route.includes('access')) return 'Access Management';
        if(route.includes('trash') || route.includes('archive')) return 'Record Management';

        return 'FaresHunt CRM';
    }

    const titleBlock = h1.parentElement;
    if(!titleBlock) return;

    titleBlock.classList.add('fh-standard-title-block');

    // Hide existing page-level label if it is a direct sibling of the H1.
    Array.from(titleBlock.children).forEach(function(el){
        if(el === h1) return;
        if(el.classList.contains('fh-page-eyebrow')) return;

        const cls = (el.className || '').toString().toLowerCase();
        const isExistingEyebrow =
            cls.includes('eyebrow') ||
            cls.includes('page-kicker') ||
            cls.includes('title-kicker') ||
            (el.tagName === 'SPAN' && cls.includes('pill') && el.previousElementSibling === null);

        if(isExistingEyebrow){
            el.style.setProperty('display','none','important');
        }
    });

    const eyebrow = document.createElement('div');
    eyebrow.className = 'fh-page-eyebrow';
    eyebrow.textContent = eyebrowFor(routeName);

    titleBlock.insertBefore(eyebrow, h1);
    h1.dataset.fhTitleStandardized = '1';
});
</script>

</body></html>