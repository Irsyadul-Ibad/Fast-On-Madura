    @push('page-styles')
    <style>
        .detail-modal { position:fixed; inset:0; z-index:20; display:grid; place-items:center; padding:20px; background:rgba(0,0,0,.45); }
        .detail-modal[hidden] { display:none; }
        .detail-modal-box { position:relative; width:min(620px, 100%); max-height:90vh; overflow:auto; padding:24px; border-radius:10px; background:#fff; box-shadow:0 12px 40px rgba(0,0,0,.25); }
        .detail-modal-box h3 { margin:0 0 18px; color:#0b3d6b; }
        .detail-modal-close { position:absolute; top:10px; right:10px; padding:2px 9px; background:#e8eef5; color:#223; font-size:20px; }
        .detail-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px; }
        .detail-grid div { padding:10px; border:1px solid #d6e2ee; border-radius:6px; }
        .detail-grid small, .detail-grid b { display:block; }
        .detail-grid small { margin-bottom:4px; color:#637487; }
        .detail-grid b { overflow-wrap:anywhere; }
        .detail-full { grid-column:1 / -1; }
        .detail-rab-form { display:flex; align-items:end; gap:10px; margin-top:18px; }
        .detail-rab-form label { display:block; flex:1; font-weight:600; }
        .detail-rab-input { display:flex; align-items:center; margin-top:5px; border:1px solid #b9c7d5; border-radius:6px; overflow:hidden; }
        .detail-rab-input span { padding:8px; background:#eef3f8; }
        .detail-rab-input input { width:150px; padding:8px; border:0; outline:0; }
        .kebutuhan-tabel { width:100%; margin:0 0 16px; border:1px solid #c8d6e5; border-radius:6px; overflow:hidden; font-size:12px; }
        .kebutuhan-tabel th, .kebutuhan-tabel td { padding:8px; border-bottom:1px solid #dce5ee; text-align:left; }
        .kebutuhan-tabel thead th { background:#eef3f8; color:#536477; }
        .kebutuhan-tabel tbody tr:last-child th, .kebutuhan-tabel tbody tr:last-child td { border-bottom:0; }
        .kebutuhan-tabel select, .kebutuhan-tabel input { width:100%; padding:6px; border:1px solid #c8d6e5; border-radius:4px; background:#fff8ee; }
        .tujuan-hidden-select{position:absolute!important;width:1px!important;height:1px!important;padding:0!important;margin:-1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important}
        #sendModal .detail-modal-box{width:min(560px,100%)!important;padding:0!important;border-radius:14px!important;overflow:auto!important;background:#f8fafc!important}
        #sendModal .detail-modal-box h3{margin:0!important;padding:16px 20px!important;background:#0d1b8c!important;color:#fff!important;font-size:15px!important;line-height:1.3!important}
        #sendModal .detail-modal-box form{padding:16px 20px 18px!important}#sendModal .detail-modal-box form>label{display:block;margin-bottom:9px;color:#243b53;font-size:12px;font-weight:700}
        .tujuan-cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin:0 0 16px}
        .tujuan-card{display:flex!important;align-items:center;flex-direction:column;justify-content:center;gap:4px!important;min-height:102px!important;padding:12px 8px!important;border:1px solid #d9e2ec!important;border-radius:10px!important;background:#fff!important;color:#243b53!important;box-shadow:none!important;text-align:center;cursor:pointer;transition:border-color .18s,background .18s,transform .18s!important}
        .tujuan-card:hover{border-color:#2b73fe!important;background:#f4f8ff!important;transform:translateY(-1px)!important}.tujuan-card.is-selected{border-color:#2b73fe!important;background:#eef4ff!important;box-shadow:inset 0 0 0 2px rgba(43,115,254,.16)!important}
        .tujuan-card-icon{display:grid;place-items:center;width:32px;height:32px;border-radius:50%;background:#e9edff;color:#293bc0;font-size:20px;font-weight:800;line-height:1}.tujuan-card-icon-green{background:#d9f8e9;color:#159447}.tujuan-card strong{font-size:11px;line-height:1.2}.tujuan-card small{max-width:120px;color:#8191aa;font-size:9px;line-height:1.2}
        #sendModal #expansionNeeds{margin-top:4px;padding:12px;border:1px solid #d9e2ec;border-radius:10px;background:#fff}#sendModal .kebutuhan-tabel{margin:0!important;border-radius:8px!important;background:#fff!important}#sendModal .kebutuhan-tabel th,#sendModal .kebutuhan-tabel td{padding:9px!important}#sendModal .kebutuhan-tabel select,#sendModal .kebutuhan-tabel input{min-height:32px!important;background:#fff8ee!important;border-radius:6px!important}
        #sendModal #sendForm>button[type="submit"]{width:100%;min-height:38px;margin-top:4px;background:#0d1b8c!important;color:#fff!important;border-radius:8px!important;font-weight:700!important}
        #detailModal .detail-modal-box{display:flex!important;flex-direction:column!important;width:min(680px,100%)!important;max-height:calc(100vh - 48px)!important;padding:0!important;border:1px solid #dfe6fb!important;border-radius:24px!important;overflow:hidden!important;background:#fff!important;color:#1d2a55!important;box-shadow:0 24px 64px rgba(15,35,104,.25)!important}
        #detailModal .detail-modal-header{position:relative!important;display:flex!important;align-items:center!important;justify-content:space-between!important;gap:20px!important;padding:22px 24px 18px!important;border-bottom:1px solid #e0e7f7!important;background:#fff!important}
        #detailModal .detail-modal-header h3{margin:0!important;padding:0 54px 0 0!important;background:transparent!important;color:#1236a5!important;font-size:22px!important;font-weight:800!important;line-height:1.25!important}
        #detailModal .detail-modal-summary{margin:5px 0 0!important;color:#60719b!important;font-size:14px!important;font-weight:500!important;line-height:1.45!important;overflow-wrap:anywhere}
        #detailModal .detail-modal-close{position:absolute!important;top:18px!important;right:20px!important;z-index:2!important;display:grid!important;place-items:center!important;width:40px!important;height:40px!important;min-width:40px!important;min-height:40px!important;padding:0!important;border:0!important;border-radius:13px!important;background:#eef2ff!important;color:#394b7e!important;font-size:25px!important;font-family:Arial,sans-serif!important;font-weight:400!important;line-height:1!important;box-shadow:none!important;cursor:pointer!important}
        #detailModal .detail-modal-close:hover{background:#dfe7ff!important;color:#173daf!important}
        #detailModal .detail-modal-content{display:grid!important;gap:20px!important;overflow-y:auto!important;overscroll-behavior:contain!important;padding:20px 24px!important}
        #detailModal .detail-section{min-width:0}
        #detailModal .detail-section-title{margin:0 0 10px!important;padding:0!important;background:transparent!important;color:#60719b!important;font-size:12px!important;font-weight:800!important;letter-spacing:.12em!important;line-height:1.4!important;text-transform:uppercase!important}
        #detailModal .detail-customer-grid{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:10px!important;padding:0!important}
        #detailModal .detail-grid>div{min-width:0!important;padding:13px 14px!important;border:1px solid #dfe6fb!important;border-radius:15px!important;background:#fff!important}
        #detailModal .detail-grid small,#detailModal .detail-cost-grid small{display:block!important;margin:0 0 5px!important;color:#60719b!important;font-size:11px!important;font-weight:700!important;letter-spacing:.045em!important;line-height:1.35!important;text-transform:uppercase!important}
        #detailModal .detail-grid b,#detailModal .detail-cost-grid b{display:block!important;color:#202d5a!important;font-size:14px!important;font-weight:700!important;line-height:1.45!important;overflow-wrap:anywhere!important}
        #detailModal .detail-grid .detail-full{grid-column:1/-1!important}
        #detailModal .detail-grid .detail-value-pill{display:inline-flex!important;max-width:100%!important;align-items:center!important;padding:5px 10px!important;border-radius:999px!important;background:#e8efff!important;color:#2147bd!important;font-size:12px!important;font-weight:700!important}
        #detailModal .detail-cost-grid{display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:10px!important}
        #detailModal .detail-cost-grid>div{min-width:0!important;padding:13px 14px!important;border:1px solid #dfe6fb!important;border-radius:15px!important;background:#f2f5ff!important}
        #detailModal .detail-cost-grid b{color:#1236a5!important;font-size:17px!important}
        #detailModal .detail-cost-grid .detail-rab-status{display:inline-flex!important;width:max-content!important;max-width:100%!important;align-items:center!important;padding:5px 10px!important;border-radius:999px!important;background:#fff1c9!important;color:#916000!important;font-size:12px!important;white-space:normal!important}
        #detailModal .detail-cost-grid .detail-rab-status.is-filled{background:#dcf5e8!important;color:#176d43!important}
        #detailModal .detail-rab-form{display:grid!important;gap:10px!important;margin:0!important;padding:18px!important;border:1px solid #c5d2ff!important;border-radius:18px!important;background:#f2f5ff!important}
        #detailModal .detail-rab-heading h4{margin:0 0 3px!important;padding:0!important;background:transparent!important;color:#1236a5!important;font-size:16px!important;font-weight:800!important;line-height:1.35!important}
        #detailModal .detail-rab-heading p{margin:0!important;color:#60719b!important;font-size:13px!important;line-height:1.45!important}
        #detailModal .detail-rab-form>label{margin:0!important;color:#4f608b!important;font-size:12px!important;font-weight:700!important;line-height:1.35!important}
        #detailModal .detail-rab-controls{display:flex!important;align-items:stretch!important;gap:10px!important;min-width:0!important}
        #detailModal .detail-rab-input{display:flex!important;align-items:center!important;flex:1 1 auto!important;min-width:0!important;margin:0!important;border:1px solid #d1dcfa!important;border-radius:12px!important;background:#fff!important;overflow:hidden!important}
        #detailModal .detail-rab-input span{display:grid!important;place-items:center!important;align-self:stretch!important;min-width:48px!important;padding:0 12px!important;background:#2451dc!important;color:#fff!important;font-size:14px!important;font-weight:800!important}
        #detailModal .detail-rab-input input#rabInput{width:100%!important;min-width:0!important;min-height:48px!important;padding:10px 14px!important;border:0!important;border-radius:0!important;background:#fff!important;color:#243b53!important;font-size:15px!important;outline:0!important}
        #detailModal .detail-rab-input input#rabInput:focus{outline:0!important;box-shadow:none!important}
        #detailModal .detail-rab-input:focus-within{border-color:#2451dc!important;box-shadow:0 0 0 3px rgba(36,81,220,.13)!important}
        #detailModal .detail-rab-form .btn{display:inline-flex!important;align-items:center!important;justify-content:center!important;gap:9px!important;min-height:48px!important;padding:0 18px!important;border:0!important;border-radius:12px!important;background:#1941bd!important;color:#fff!important;font-size:14px!important;font-weight:800!important;box-shadow:0 8px 18px rgba(25,65,189,.2)!important;white-space:nowrap!important;cursor:pointer!important}
        #detailModal .detail-rab-form .btn:hover{background:#12349e!important}
        #detailModal .detail-modal-footer{display:flex!important;justify-content:flex-end!important;padding:14px 24px!important;border-top:1px solid #e0e7f7!important;background:#fff!important}
        #detailModal .detail-modal-dismiss{min-height:42px!important;padding:0 18px!important;border:1px solid #c8d5ff!important;border-radius:12px!important;background:#fff!important;color:#1236a5!important;font-size:14px!important;font-weight:700!important;box-shadow:none!important;cursor:pointer!important}
        #detailModal .detail-modal-dismiss:hover{background:#f2f5ff!important}
        @media (max-width:700px){#detailModal .detail-modal-box{max-height:calc(100vh - 24px)!important;border-radius:20px!important}#detailModal .detail-modal-header{padding:18px 18px 15px!important}#detailModal .detail-modal-close{top:14px!important;right:14px!important}#detailModal .detail-modal-content{gap:17px!important;padding:17px 18px!important}#detailModal .detail-cost-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}#detailModal .detail-modal-footer{padding:12px 18px!important}}
        @media (max-width:480px){#detailModal .detail-customer-grid{grid-template-columns:1fr!important}#detailModal .detail-grid .detail-full{grid-column:auto!important}#detailModal .detail-rab-controls{align-items:stretch!important;flex-direction:column!important}#detailModal .detail-rab-form .btn{width:100%!important}#detailModal .detail-modal-header h3{font-size:19px!important}#detailModal .detail-modal-summary{max-width:calc(100% - 34px)!important;font-size:12px!important}}
        .ikon-syarat { padding:4px 8px; background:#e8eef5; color:#0b3d6b; }
        .syarat-files { display:grid; gap:8px; padding:12px; border:1px solid #d6e2ee; border-radius:6px; }
        .syarat-files a { color:#0b3d6b; overflow-wrap:anywhere; }
        .syarat-upload-form { display:flex; gap:10px; align-items:center; margin-top:16px; flex-wrap:wrap; }
        .syarat-upload-form input[type=file] { flex:1; min-width:220px; padding:8px; border:1px solid #b9c7d5; border-radius:6px; }
        @media (max-width:600px) { .tujuan-cards{grid-template-columns:1fr}.tujuan-card{min-height:72px!important}.detail-grid { grid-template-columns:1fr; } .detail-full { grid-column:auto; } .detail-rab-form { align-items:stretch; flex-direction:column; } }
    </style>
    @endpush
