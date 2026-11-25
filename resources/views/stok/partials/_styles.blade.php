    @push('page-styles')
        <style>
            .stok-summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:16px}
            .stok-summary-card{min-height:116px;padding:18px 20px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;box-shadow:0 2px 8px rgba(15,23,42,.07)}
            .stok-summary-label{display:block;color:#64748b;font-size:11px;font-weight:800;letter-spacing:.08em}
            .stok-summary-card strong{display:block;margin-top:8px;color:#0d1b8c;font-size:28px;letter-spacing:-.04em;line-height:1}
            .stok-summary-card small{display:block;margin-top:8px;color:#94a3b8;font-size:12px}
            .stok-summary-tr{border-top:3px solid #2b73fe}.stok-summary-tm{border-top:3px solid #27a9d6}.stok-summary-tm-13{border-top-color:#0c91b9}
            .stok-summary-tr strong{color:#1d4ed8}.stok-summary-tm strong{color:#087ea4}
            .stok-alert{display:flex;align-items:flex-start;gap:9px;margin-bottom:14px;padding:12px 15px;border-radius:10px;font-size:13px;line-height:1.45}
            .stok-alert-success{border:1px solid #bce3cb;border-left:4px solid #2e9b68;background:#f1fbf5;color:#176b35;font-weight:600}
            .stok-alert-error{display:block;border:1px solid #f1c1bd;border-left:4px solid #d9534f;background:#fff5f4;color:#a33b36}
            .stok-alert-error ul{margin:5px 0 0 18px;padding:0;font-weight:500}
            .stok-alert-icon{display:inline-grid;place-items:center;width:18px;height:18px;border-radius:50%;background:#2e9b68;color:#fff;font-size:12px}
            .stok-form{padding:0;overflow:hidden}
            .stok-form-heading{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:21px 22px 17px;border-bottom:1px solid #e2e8f0}
            .stok-form-heading h2{margin:0;color:#1e293b;font-size:17px}.stok-form-heading p{margin:5px 0 0;color:#64748b;font-size:12px}
            .stok-table-scroll{border:0;border-radius:0}.stok-table{width:100%;min-width:880px}.stok-table th{font-size:10px;letter-spacing:.06em}.stok-table th,.stok-table td{padding:13px 16px}.stok-number{width:65px;text-align:center!important;color:#64748b}.stok-column{width:175px;text-align:center!important}.stok-total-column{width:130px;text-align:center!important}
            .stok-column-tr{background:#1d4ed8!important}.stok-column-tm{background:#087ea4!important}.stok-column-tm-13{background:#0c91b9!important}
            .stok-vendor-name{display:flex;align-items:center;gap:11px;min-width:250px}.stok-vendor-mark{display:grid;place-items:center;flex:0 0 32px;width:32px;height:32px;border-radius:9px;background:#eff6ff;color:#2b73fe;font-size:13px;font-weight:800}.stok-vendor-name strong{display:block;color:#1e3a5f;font-size:13px;white-space:normal}.stok-vendor-name small{display:block;margin-top:3px;color:#94a3b8;font-size:11px}
            .stok-input-cell{text-align:center!important}.stok-input{width:105px;min-height:37px;padding:7px 9px;border:1px solid #cbd5e1;border-radius:7px;text-align:right;font-size:14px;font-weight:700}.stok-input-tr{background:#f8fbff}.stok-input-tm{background:#f4fbfd}.stok-input-tm-13{background:#f2fbfd}.stok-input-tr .stok-input{border-color:#b9d0fc;color:#1d4ed8}.stok-input-tm .stok-input{border-color:#b6dfeb;color:#087ea4}.stok-input-tm-13 .stok-input{border-color:#a8dce9;color:#087ea4}.stok-input-cell>span{margin-left:5px;color:#94a3b8;font-size:11px}.stok-row-total{color:#64748b;text-align:center!important;font-size:12px}.stok-row-total strong{color:#1e293b;font-size:14px}
            .stok-form-footer{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:16px 22px;border-top:1px solid #e2e8f0;background:#fbfdff}.stok-form-footer p{margin:0;color:#64748b;font-size:12px}.stok-info-icon{display:inline-grid;place-items:center;width:16px;height:16px;margin-right:4px;border:1px solid #94a3b8;border-radius:50%;font-size:10px;font-weight:700}.stok-save-button{display:inline-flex;align-items:center;gap:7px;padding:9px 15px}.stok-save-button svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}.stok-empty{padding:28px 22px;color:#64748b;font-size:13px}.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
            @media(max-width:900px){.stok-summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
            @media(max-width:700px){.stok-summary-grid{grid-template-columns:1fr}.stok-form-heading,.stok-form-footer{align-items:flex-start;flex-direction:column}.stok-save-button{width:100%;justify-content:center}.stok-form-footer p{line-height:1.5}}

            body:has(#stokTiangPage),body:has(#stokTiangPage) main,body:has(#stokTiangPage) .content{background:#eaf0fc!important}
            #stokTiangPage{width:100%;min-width:0;color:#24345f}
            #stokTiangPage .stok-summary-grid{grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:12px!important;margin:0 0 16px!important}
            #stokTiangPage .stok-summary-card{display:flex!important;min-width:0!important;min-height:104px!important;flex-direction:column!important;justify-content:flex-start!important;gap:0!important;padding:15px 16px!important;border:1px solid #e3e9f9!important;border-radius:20px!important;background:#fff!important;box-shadow:0 8px 22px rgba(45,66,128,.06)!important}
            #stokTiangPage .stok-summary-card:hover{transform:none!important;border-color:#d8e2f7!important;box-shadow:0 8px 22px rgba(45,66,128,.06)!important}
            #stokTiangPage .stok-summary-label{display:flex!important;align-items:center!important;gap:7px!important;color:#5d6f99!important;font-size:11px!important;font-weight:800!important;letter-spacing:.07em!important;line-height:1.3!important}
            #stokTiangPage .stok-summary-label::before{content:"";display:inline-block;flex:0 0 8px;width:8px;height:8px;border-radius:50%;background:#929dbc}
            #stokTiangPage .stok-summary-tr .stok-summary-label::before{background:#129e57}
            #stokTiangPage .stok-summary-tm-11 .stok-summary-label::before{background:#efa900}
            #stokTiangPage .stok-summary-tm-13 .stok-summary-label::before{background:#8450e8}
            #stokTiangPage .stok-summary-card strong{margin:9px 0 0!important;color:#1236a5!important;font-size:28px!important;font-weight:800!important;letter-spacing:-.04em!important;line-height:1!important}
            #stokTiangPage .stok-summary-card small{margin-top:7px!important;color:#60719b!important;font-size:12px!important;line-height:1.35!important}
            #stokTiangPage .stok-form{min-width:0!important;margin:0!important;padding:0!important;overflow:hidden!important;border:1px solid #e2e8f7!important;border-radius:24px!important;background:#fff!important;box-shadow:0 10px 28px rgba(45,66,128,.07)!important}
            #stokTiangPage .stok-form-heading{min-height:72px!important;padding:17px 20px 14px!important;border:0!important;background:#fff!important}
            #stokTiangPage .stok-form-heading h2{margin:0!important;color:#1236a5!important;font-size:17px!important;font-weight:800!important;letter-spacing:-.02em!important;line-height:1.3!important}
            #stokTiangPage .stok-form-heading p{margin:4px 0 0!important;color:#60719b!important;font-size:12px!important;line-height:1.45!important}
            #stokTiangPage .stok-form-heading .pill{min-height:30px!important;padding:0 12px!important;border:0!important;border-radius:999px!important;background:#e8eeff!important;color:#2149c1!important;font-size:12px!important;font-weight:750!important;white-space:nowrap!important}
            #stokTiangPage .stok-table-scroll{width:calc(100% - 32px)!important;max-width:calc(100% - 32px)!important;margin:0 16px!important;overflow-x:auto!important;overflow-y:visible!important;border:1px solid #dfe6fb!important;border-radius:16px!important;background:#fff!important;scrollbar-width:thin;scrollbar-color:#cfd9f2 #f5f7ff}
            #stokTiangPage .stok-table{width:100%!important;min-width:970px!important;border:0!important;border-radius:16px!important;background:#fff!important;color:#35476f!important;font-size:12px!important;line-height:1.4!important}
            #stokTiangPage .stok-table th,#stokTiangPage .stok-table td{height:68px!important;padding:12px 14px!important;border-right:0!important;border-bottom:1px solid #e6ebf8!important;vertical-align:middle!important}
            #stokTiangPage .stok-table thead th{height:44px!important;position:sticky!important;top:0!important;z-index:2!important;border-bottom:1px solid #e1e7f7!important;background:#f0f4ff!important;color:#52638b!important;font-size:10px!important;font-weight:800!important;letter-spacing:.06em!important;text-align:center!important}
            #stokTiangPage .stok-table thead th:nth-child(2){text-align:left!important}
            #stokTiangPage .stok-table thead th.stok-column-tr,#stokTiangPage .stok-table thead th.stok-column-tm,#stokTiangPage .stok-table thead th.stok-column-tm-13{background:#f0f4ff!important;color:#52638b!important}
            #stokTiangPage .stok-table tbody tr:nth-child(even),#stokTiangPage .stok-table tbody tr:hover{background:#fff!important;box-shadow:none!important}
            #stokTiangPage .stok-table tbody tr:last-child td{border-bottom:0!important}
            #stokTiangPage .stok-number{width:64px!important;color:#64759d!important;text-align:center!important}
            #stokTiangPage .stok-table td:nth-child(2){min-width:340px!important}
            #stokTiangPage .stok-column{width:170px!important;text-align:center!important}
            #stokTiangPage .stok-total-column{width:130px!important;text-align:center!important}
            #stokTiangPage .stok-vendor-name{display:flex!important;align-items:center!important;gap:12px!important;min-width:0!important}
            #stokTiangPage .stok-vendor-mark{display:grid!important;place-items:center!important;flex:0 0 36px!important;width:36px!important;height:36px!important;border-radius:12px!important;background:#e8eeff!important;color:#2451dc!important;font-size:14px!important;font-weight:800!important}
            #stokTiangPage .stok-vendor-name strong{display:block!important;color:#1236a5!important;font-size:13px!important;font-weight:800!important;line-height:1.35!important;white-space:normal!important}
            #stokTiangPage .stok-vendor-name small{display:block!important;margin-top:3px!important;color:#60719b!important;font-size:11px!important;line-height:1.3!important}
            #stokTiangPage .stok-input-cell{text-align:center!important;white-space:nowrap!important}
            #stokTiangPage .stok-input-tr{background:#f8faff!important}
            #stokTiangPage .stok-input-tm{background:#f8faff!important}
            #stokTiangPage .stok-input-tm-13{background:#f8faff!important}
            #stokTiangPage .stok-input{width:84px!important;min-height:40px!important;padding:7px 10px!important;border:1px solid #d5def7!important;border-radius:12px!important;background:#fbfcff!important;color:#2451c6!important;font-size:14px!important;font-weight:800!important;text-align:center!important;box-shadow:none!important;appearance:textfield}
            #stokTiangPage .stok-input::-webkit-inner-spin-button,#stokTiangPage .stok-input::-webkit-outer-spin-button{margin:0;appearance:none}
            #stokTiangPage .stok-input:focus{outline:0!important;border-color:#2451dc!important;background:#fff!important;box-shadow:0 0 0 3px rgba(36,81,220,.12)!important}
            #stokTiangPage .stok-input-tr .stok-input,#stokTiangPage .stok-input-tm .stok-input,#stokTiangPage .stok-input-tm-13 .stok-input{border-color:#d5def7!important;color:#2451c6!important}
            #stokTiangPage .stok-input-cell>span{margin-left:5px!important;color:#60719b!important;font-size:11px!important}
            #stokTiangPage .stok-row-total{color:#60719b!important;text-align:center!important;font-size:11px!important;white-space:nowrap!important}
            #stokTiangPage .stok-row-total strong{color:#24345f!important;font-size:13px!important;font-weight:800!important}
            #stokTiangPage .stok-form-footer{display:flex!important;justify-content:space-between!important;align-items:center!important;gap:14px!important;padding:15px 20px!important;border:0!important;background:#fff!important}
            #stokTiangPage .stok-form-footer p{margin:0!important;color:#60719b!important;font-size:12px!important;line-height:1.45!important}
            #stokTiangPage .stok-info-icon{display:inline-grid!important;place-items:center!important;width:17px!important;height:17px!important;margin-right:5px!important;border:1.5px solid #60719b!important;border-radius:50%!important;color:#60719b!important;font-size:10px!important;font-style:normal!important;font-weight:800!important}
            #stokTiangPage .stok-save-button{display:inline-flex!important;align-items:center!important;justify-content:center!important;gap:9px!important;min-height:46px!important;padding:0 18px!important;border:0!important;border-radius:13px!important;background:linear-gradient(110deg,#2451dc,#1236a5)!important;color:#fff!important;font-size:14px!important;font-weight:800!important;box-shadow:0 9px 20px rgba(25,65,189,.23)!important;white-space:nowrap!important}
            #stokTiangPage .stok-save-button:hover{background:linear-gradient(110deg,#1d46ca,#0e2e95)!important;transform:translateY(-1px)!important}
            #stokTiangPage .stok-save-button svg{width:17px!important;height:17px!important}
            #stokTiangPage .stok-empty{margin:0 16px!important;padding:26px 18px!important;border:1px dashed #d5def7!important;border-radius:14px!important;background:#f8faff!important;color:#60719b!important;font-size:13px!important}
            #stokTiangPage .stok-alert{border-radius:13px!important;box-shadow:0 4px 12px rgba(45,66,128,.05)!important}
            @media(max-width:1100px){#stokTiangPage .stok-summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}}
            @media(max-width:700px){#stokTiangPage .stok-summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:9px!important}#stokTiangPage .stok-summary-card{min-height:96px!important;padding:13px!important;border-radius:17px!important}#stokTiangPage .stok-summary-card strong{font-size:25px!important}#stokTiangPage .stok-form-heading{align-items:flex-start!important;padding:16px!important}#stokTiangPage .stok-form-heading .pill{margin-top:2px}#stokTiangPage .stok-table-scroll{width:calc(100% - 20px)!important;max-width:calc(100% - 20px)!important;margin:0 10px!important}#stokTiangPage .stok-form-footer{align-items:stretch!important;flex-direction:column!important;padding:14px 16px!important}#stokTiangPage .stok-save-button{width:100%!important}}
            @media(max-width:430px){#stokTiangPage .stok-summary-grid{grid-template-columns:1fr!important}#stokTiangPage .stok-form-heading{flex-direction:column!important}#stokTiangPage .stok-form-heading .pill{align-self:flex-start!important}}
        </style>
    @endpush
