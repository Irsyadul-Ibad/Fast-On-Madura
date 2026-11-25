@push('page-styles')
<style>
    .page-actions .notification-mark-all-form{margin:0}
    .page-actions .notification-mark-all{display:inline-flex;align-items:center;justify-content:center;gap:10px;min-height:44px;padding:9px 18px!important;border:1px solid #cbd8ff!important;border-radius:14px!important;background:#fff!important;color:#102cae!important;font-size:14px!important;font-weight:750!important;box-shadow:none!important;white-space:nowrap}
    .page-actions .notification-mark-all:hover{background:#f5f7ff!important;border-color:#aebfff!important;color:#102cae!important;box-shadow:none!important;transform:none!important}
    .page-actions .notification-mark-all svg{display:block;width:19px;height:19px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
    .main-content.main-notification{padding-top:0!important}
    .notification-page{display:grid;gap:16px;min-width:0}
    .notification-tabs{display:flex;align-items:center;flex-wrap:wrap;gap:8px}
    .notification-tab{display:inline-flex;align-items:center;gap:4px;min-height:40px;padding:8px 16px;border:1px solid transparent;border-radius:14px;background:#fff;color:#344a81;font-size:13px;font-weight:700;line-height:1.25;text-decoration:none;transition:background-color .18s ease,color .18s ease,box-shadow .18s ease}
    .notification-tab:hover{background:#f5f7ff;color:#214fe0}
    .notification-tab.is-active{background:#214fe0;color:#fff;box-shadow:0 8px 18px rgba(33,78,224,.18)}
    .notification-board{min-width:0;padding:24px 20px;border:0;border-radius:26px;background:#fff;box-shadow:0 12px 30px rgba(50,72,134,.07)}
    .notification-group+.notification-group{margin-top:24px}
    .notification-date{margin:0 0 10px 4px;color:#5e719e;font-size:12px;font-weight:800;letter-spacing:.14em;line-height:1.4;text-transform:uppercase}
    .notification-list{display:grid;gap:8px}
    .notification-form{margin:0}
    .notification-card{display:grid;grid-template-columns:46px minmax(0,1fr) auto 8px;align-items:center;gap:16px;width:100%;min-height:76px;margin:0;padding:14px 16px;border:1px solid #e0e7f8!important;border-radius:17px!important;background:#f8faff!important;color:#263867!important;text-align:left;box-shadow:none!important;transition:background-color .18s ease,border-color .18s ease,transform .18s ease}
    .notification-card:hover{background:#f3f6ff!important;border-color:#cbd8ff!important;box-shadow:0 5px 14px rgba(50,72,134,.08)!important;transform:translateY(-1px)}
    .notification-card:focus-visible{outline:3px solid rgba(33,78,224,.24);outline-offset:2px}
    .notification-card.is-read{background:#fff!important}
    .notification-icon{display:grid;place-items:center;width:46px;height:46px;border-radius:14px;background:#e4ebff;color:#214fe0}
    .notification-icon svg,.notification-empty-icon svg{display:block;width:21px;height:21px;fill:none;stroke:currentColor;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
    .notification-icon--approved{background:#dcf4e8;color:#197847}
    .notification-icon--returned{background:#fde6e5;color:#c1352b}
    .notification-icon--document{background:#fff1c9;color:#9a6500}
    .notification-copy{display:grid;gap:3px;min-width:0}
    .notification-copy strong{color:#102cae;font-size:14px;font-weight:800;line-height:1.35}
    .notification-copy>span{color:#344a81;font-size:13px;font-weight:500;line-height:1.45;overflow-wrap:anywhere}
    .notification-copy .notification-agenda{color:#102cae;font-weight:800}
    .notification-time{color:#5e719e;font-size:12px;font-weight:600;white-space:nowrap}
    .notification-unread-dot{display:block;width:9px;height:9px;border-radius:50%;background:#214fe0}
    .notification-empty{display:grid;justify-items:center;gap:8px;padding:48px 16px;color:#5e719e;text-align:center}
    .notification-empty-icon{display:grid;place-items:center;width:48px;height:48px;margin-bottom:4px;border-radius:15px;background:#eef2ff;color:#214fe0}
    .notification-empty strong{color:#102cae;font-size:15px}
    .notification-empty>span:last-child{font-size:13px}
    .notification-success{padding:12px 16px;border:1px solid #bfe8d0;border-radius:14px;background:#edf9f2;color:#176b3d;font-size:13px;font-weight:650}
    .notification-pagination{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-top:18px;padding-top:16px;border-top:1px solid #e0e7f8}
    .notification-results{margin:0;color:#5e719e;font-size:13px;line-height:1.4}
    .notification-results strong{color:#344a81;font-weight:750}
    .notification-page-links{display:flex;align-items:center;gap:8px}
    .notification-page-number,.notification-page-arrow{display:inline-flex;align-items:center;justify-content:center;flex:0 0 40px;width:40px;height:40px;border:0;border-radius:13px;background:#f0f4ff;color:#344a81;font-size:13px;font-weight:750;text-decoration:none;transition:background-color .18s ease,color .18s ease,box-shadow .18s ease}
    .notification-page-number:hover,.notification-page-arrow:not(.is-disabled):hover{background:#e3eaff;color:#214fe0}
    .notification-page-number.is-current{background:linear-gradient(135deg,#214ee0,#102eaf);color:#fff;box-shadow:0 8px 18px rgba(33,78,224,.2)}
    .notification-page-arrow.is-disabled{color:#a9b5d1;cursor:default}
    .notification-page-arrow svg{display:block;width:18px;height:18px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
    @media(max-width:650px){
        body.notification-screen .content>header.page-topbar{align-items:flex-start;flex-wrap:wrap;gap:10px;min-height:0}
        body.notification-screen .content>header.page-topbar .header-title{flex:1 1 100%;align-items:center}
        body.notification-screen .content>header.page-topbar .page-actions{width:100%;justify-content:flex-start}
        body.notification-screen .notification-mark-all-form,body.notification-screen .page-actions .notification-mark-all{width:100%}
        .page-actions .notification-mark-all{min-height:40px;padding:8px 11px!important;font-size:12px!important}
        .notification-page{gap:12px}
        .notification-tabs{gap:6px}
        .notification-tab{min-height:38px;padding:8px 11px;font-size:12px}
        .notification-board{padding:16px 12px;border-radius:20px}
        .notification-group+.notification-group{margin-top:20px}
        .notification-card{grid-template-columns:42px minmax(0,1fr) 8px;gap:8px 11px;min-height:0;padding:12px}
        .notification-icon{grid-column:1;grid-row:1 / span 2;width:42px;height:42px;border-radius:13px}
        .notification-copy{grid-column:2;grid-row:1}
        .notification-copy strong{font-size:13px}
        .notification-copy>span{font-size:12px}
        .notification-time{grid-column:2;grid-row:2}
        .notification-unread-dot{grid-column:3;grid-row:1;align-self:start;margin-top:4px}
        .notification-pagination{align-items:flex-start;flex-direction:column}
        .notification-page-links{align-self:flex-end}
    }
</style>
@endpush
