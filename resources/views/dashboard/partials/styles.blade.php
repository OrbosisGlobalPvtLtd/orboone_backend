<style>
    :root{
        --dash-primary:var(--orb-primary, #4B00E8);
        --dash-secondary:var(--orb-secondary, #FF5252);
        --dash-pink:#D400D5;
        --dash-rose:#EC4E74;
        --dash-yellow:#FFB101;
        --dash-bg:#F6F7FB;
        --dash-surface:#FFFFFF;
        --dash-border:#E7EAF3;
        --dash-text:#101828;
        --dash-muted:#667085;
        --dash-green:#12B76A;
        --dash-blue:#2E90FA;
        --dash-shadow:0 14px 34px rgba(16,24,40,.07);
        --dash-shadow-soft:0 8px 22px rgba(16,24,40,.05);
    }
    .dash-page{min-height:calc(100vh - 90px);padding:12px 8px 30px;background:var(--dash-bg);}
    .dash-wrap{max-width:1440px;margin:0 auto;}
    .dash-hero{position:relative;overflow:hidden;border-radius:24px;padding:24px;background:linear-gradient(135deg,var(--orb-primary, #4B00E8) 0%,var(--orb-secondary, #FF5252) 100%);color:#fff;box-shadow:0 18px 44px rgba(75,0,232,.18);margin-bottom:16px;}
    .dash-hero:before,.dash-hero:after{content:"";position:absolute;border-radius:999px;background:rgba(255,255,255,.10);}
    .dash-hero:before{width:220px;height:220px;right:-70px;top:-80px;}
    .dash-hero:after{width:170px;height:170px;right:120px;bottom:-95px;}
    .dash-hero-inner{position:relative;z-index:1;display:flex;justify-content:space-between;gap:18px;align-items:flex-end;flex-wrap:wrap;}
    .dash-eyebrow{font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.72);margin-bottom:8px;}
    .dash-title{margin:0;font-size:28px;font-weight:900;color:#fff;}
    .dash-subtitle{margin:8px 0 0;color:rgba(255,255,255,.78);font-weight:650;}
    .dash-hero-metrics{display:flex;gap:10px;flex-wrap:wrap;}
    .dash-mini{min-width:130px;padding:12px 14px;border-radius:16px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.18);}
    .dash-mini span{display:block;font-size:11px;font-weight:850;color:rgba(255,255,255,.72);text-transform:uppercase;letter-spacing:.5px;}
    .dash-mini strong{display:block;margin-top:4px;font-size:18px;color:#fff;}
    .dash-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:16px;}
    .dash-card,.dash-panel,.dash-action,.dash-alert{background:var(--dash-surface);border:1px solid var(--dash-border);border-radius:18px;box-shadow:var(--dash-shadow-soft);}
    .dash-card{padding:16px;position:relative;overflow:hidden;min-height:132px;}
    .dash-card:before{content:"";position:absolute;left:0;top:0;width:100%;height:4px;background:linear-gradient(90deg,var(--dash-primary),var(--dash-pink));}
    .dash-card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;}
    .dash-card-label{font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.55px;color:var(--dash-muted);}
    .dash-card-value{margin-top:8px;font-size:25px;font-weight:900;color:var(--dash-text);line-height:1.15;word-break:break-word;}
    .dash-card-sub{margin-top:10px;color:var(--dash-muted);font-size:12px;font-weight:650;line-height:1.45;}
    .dash-icon{width:48px;height:48px;border-radius:15px;display:flex;align-items:center;justify-content:center;background:rgba(75,0,232,.09);color:var(--dash-primary);font-size:18px;flex:0 0 auto;}
    .dash-section{margin-bottom:16px;}
    .dash-section-title{display:flex;align-items:center;gap:10px;margin:0 0 12px;font-size:16px;font-weight:900;color:var(--dash-text);}
    .dash-section-title i{width:34px;height:34px;border-radius:12px;background:rgba(75,0,232,.08);display:flex;align-items:center;justify-content:center;color:var(--dash-primary);}
    .dash-two{display:grid;grid-template-columns:1.15fr .85fr;gap:14px;}
    .dash-three{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;}
    .dash-panel{padding:16px;}
    .dash-actions{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;}
    .dash-action{display:block;padding:15px;text-decoration:none !important;color:var(--dash-text);transition:.18s ease;min-height:118px;}
    .dash-action:hover{transform:translateY(-3px);box-shadow:var(--dash-shadow);color:var(--dash-text);}
    .dash-action-icon{width:42px;height:42px;border-radius:14px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--dash-primary),var(--dash-secondary));color:#fff;margin-bottom:12px;}
    .dash-action strong{display:block;font-size:13px;font-weight:900;}
    .dash-action span{display:block;margin-top:4px;color:var(--dash-muted);font-size:12px;font-weight:650;}
    .dash-pill{display:inline-flex;align-items:center;gap:6px;padding:7px 10px;border-radius:999px;background:#F2F4F7;color:var(--dash-muted);font-size:11px;font-weight:900;text-transform:uppercase;}
    .dash-pill.green{background:rgba(18,183,106,.12);color:#087443;}
    .dash-pill.red{background:rgba(236,78,116,.13);color:#B42318;}
    .dash-pill.blue{background:rgba(46,144,250,.12);color:#175CD3;}
    .dash-pill.yellow{background:rgba(255,177,1,.16);color:#946200;}
    .dash-stat-list{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;}
    .dash-stat{padding:12px;border:1px solid var(--dash-border);border-radius:14px;background:#FCFCFD;}
    .dash-stat span{display:block;color:var(--dash-muted);font-size:11px;font-weight:850;text-transform:uppercase;}
    .dash-stat strong{display:block;margin-top:5px;color:var(--dash-text);font-size:20px;font-weight:900;}
    .dash-bar-row{display:grid;grid-template-columns:110px 1fr 45px;gap:10px;align-items:center;margin:10px 0;}
    .dash-bar-label{font-size:12px;font-weight:800;color:var(--dash-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .dash-bar-track{height:9px;border-radius:999px;background:#EEF2F7;overflow:hidden;}
    .dash-bar-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--dash-primary),var(--dash-pink));min-width:3px;}
    .dash-bar-value{text-align:right;font-size:12px;font-weight:900;color:var(--dash-text);}
    .dash-table{width:100%;margin:0;}
    .dash-table th{background:#F8FAFC;color:var(--dash-muted);font-size:11px;font-weight:900;text-transform:uppercase;border-bottom:1px solid var(--dash-border);padding:11px;}
    .dash-table td{padding:11px;border-bottom:1px solid #F1F3F8;color:var(--dash-text);font-size:13px;font-weight:650;vertical-align:middle;}
    .dash-empty{padding:28px;text-align:center;color:var(--dash-muted);font-weight:700;}
    .dash-alert{padding:14px 16px;margin-bottom:16px;color:var(--dash-muted);font-weight:750;}
    .dash-svg{width:100%;height:170px;display:block;}
    .dash-svg text{font-size:10px;fill:#98A2B3;font-weight:700;}
    .dash-svg .present{fill:none;stroke:var(--dash-primary);stroke-width:3;}
    .dash-svg .late{fill:none;stroke:var(--dash-rose);stroke-width:2;stroke-dasharray:4 4;}
    @media(max-width:1200px){.dash-grid{grid-template-columns:repeat(3,minmax(0,1fr));}.dash-actions{grid-template-columns:repeat(3,minmax(0,1fr));}}
    @media(max-width:992px){.dash-grid,.dash-three{grid-template-columns:repeat(2,minmax(0,1fr));}.dash-two{grid-template-columns:1fr;}.dash-stat-list{grid-template-columns:repeat(2,minmax(0,1fr));}}
    @media(max-width:640px){.dash-page{padding:6px 0 20px;}.dash-hero{border-radius:20px;padding:18px;}.dash-title{font-size:22px;}.dash-grid,.dash-three,.dash-actions,.dash-stat-list{grid-template-columns:1fr;}.dash-mini{flex:1;min-width:calc(50% - 5px);}.dash-bar-row{grid-template-columns:82px 1fr 36px;}}
</style>
