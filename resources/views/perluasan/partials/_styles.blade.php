    @push('page-styles')
    <style>
        .content .kartu .kartu-judul>span:first-child{font-size:16px!important;font-weight:800!important;letter-spacing:.015em!important}
        .content .kartu .kartu-judul .pill{font-size:12px!important}
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
        .detail-edit-table { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:10px; margin:18px 0; }
        .detail-edit-table label { font-weight:600; color:#536477; }
        .detail-edit-table input, .detail-edit-table select { display:block; width:100%; margin-top:5px; padding:8px; border:1px solid #b9c7d5; border-radius:5px; background:#fff8ee; }
        .vendor-form { margin-top:20px; padding-top:16px; border-top:1px solid #d6e2ee; display:grid; gap:10px; }
        .vendor-form h4 { margin:0; color:#0b3d6b; }
        .vendor-form label { display:grid; gap:5px; font-weight:600; color:#536477; }
        .vendor-form select, .vendor-form input[type=file] { width:100%; padding:8px; border:1px solid #b9c7d5; border-radius:5px; background:#fff; }
        .vendor-stock-info{display:grid;gap:8px;padding:10px 12px;border:1px solid #c8ddef;border-radius:8px;background:#f3f8ff;color:#536477;font-size:12px}
        .vendor-stock-heading{display:flex;align-items:center;justify-content:space-between;gap:10px}.vendor-stock-heading>span:first-child{font-weight:700;color:#123b5d}.vendor-stock-availability{font-size:11px;font-weight:700}.vendor-stock-availability.is-ready{color:#16834b}.vendor-stock-availability.is-empty{color:#c23f3a}
        .vendor-stock-values{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.vendor-stock-values-three{grid-template-columns:repeat(3,minmax(0,1fr))}.vendor-stock-values span{display:flex;align-items:baseline;gap:4px;padding:7px 9px;border-radius:6px;background:#fff;border:1px solid #d9e6f2}.vendor-stock-values b{color:#1e6fa8}.vendor-stock-values strong{margin-left:auto;color:#123b5d;font-size:15px}.vendor-stock-values small{color:#718096}
        .vendor-stock-warning{padding:10px 12px;border:1px solid #f1d28a;border-radius:8px;background:#fffaf0;color:#8a6417;font-size:12px;line-height:1.45}.vendor-stock-warning[hidden],.vendor-stock-info[hidden]{display:none!important}
        .vendor-kelayakan { display:flex; gap:12px; align-items:center; flex-wrap:wrap; color:#536477; }
        .vendor-kelayakan label { display:inline-flex; align-items:center; gap:4px; }
        .vendor-delete-card{margin-top:12px;padding:14px;border:1px solid #e2caca;border-radius:9px;background:#fff8f7}
        .vendor-delete-card[hidden]{display:none!important}
        .vendor-delete-card h4{margin:0 0 5px;color:#8d3030}
        .vendor-delete-card p{margin:0;color:#6b5353;font-size:12px;line-height:1.45}
        .vendor-delete-form{margin-top:10px}
        .vendor-delete-form button{width:100%;background:#c23f3a!important;color:#fff!important}
        .vendor-delete-form button:hover{background:#a9322e!important}
        .vendor-locked-notice{padding:10px 12px;border:1px solid #f1d28a;border-radius:8px;background:#fffaf0;color:#8a6417;font-size:12px;line-height:1.45}
        .vendor-report-result { display:grid; gap:8px; margin-top:16px; padding:14px; border:1px solid #c8d6e5; border-radius:8px; background:#eef7ff; }
        .vendor-report-result h4 { margin:0 0 4px; color:#0b3d6b; }
        .vendor-report-result div { display:flex; justify-content:space-between; gap:12px; color:#536477; }
        .vendor-report-result b { color:#223; }
        .planning-result-box { display:grid; gap:10px; margin-top:16px; padding:14px; border:1px solid #c8d6e5; border-radius:8px; background:#f7fbff; }
        .planning-result-box h4 { margin:0; color:#0b3d6b; }
        .planning-result-box a { color:#0b3d6b; }
        .construction-report-box { display:grid; gap:10px; margin-top:16px; padding:14px; border:1px solid #c8d6e5; border-radius:8px; background:#f3f8ff; }
        .construction-report-box h4 { margin:0; color:#0b3d6b; }
        .construction-report-box a { color:#0b3d6b; }
        .berkas-ulp-cell{display:flex;flex-direction:column;align-items:flex-start;gap:5px;min-width:105px}
        .berkas-ulp-cell a{display:inline-flex;align-items:center;gap:5px;padding:4px 7px;border:1px solid #c8def5;border-radius:7px;background:#f3f8ff;color:#1e6fa8!important;font-size:10px;font-weight:700;text-decoration:none;white-space:nowrap}
        .berkas-ulp-cell a::before{content:'↗';font-size:10px}
        .berkas-ulp-cell a:hover{background:#1e6fa8;color:#fff!important;border-color:#1e6fa8}
        .berkas-ulp-empty{color:#94a3b8;font-size:10px}
        .planning-result-box input[type=file] { padding:8px; border:1px solid #b9c7d5; border-radius:6px; background:#fff; }
        .detail-modal-box .planning-result-box{display:grid!important;gap:11px!important;margin-top:12px!important;padding:15px!important;border:1px solid #d9e2ec!important;border-radius:12px!important;background:#f8fbff!important;box-shadow:0 2px 8px rgba(18,59,93,.05)!important}
        .detail-modal-box .planning-result-box h4{margin:0!important;padding-bottom:8px!important;border-bottom:1px solid #e2e8f0!important;color:#123b5d!important;font-size:14px!important;line-height:1.35!important}
        .detail-modal-box .planning-result-box>div[id$="Files"]{min-height:32px;padding:8px 10px;border:1px solid #d9e2ec;border-radius:8px;background:#fff;color:#627d98;font-size:12px;line-height:1.4;overflow-wrap:anywhere}
        .detail-modal-box .planning-result-box>div[id$="Files"] a{color:#1e6fa8!important;font-weight:600;text-decoration:underline;overflow-wrap:anywhere}
        .detail-modal-box .result-file-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0;padding:7px 0}
        .detail-modal-box .result-file-row+.result-file-row{border-top:1px solid #edf1f6}
        .detail-modal-box .result-file-info{display:flex;align-items:center;justify-content:space-between;gap:12px;min-width:0;flex:1}
        .detail-modal-box .result-file-info a{min-width:0;overflow-wrap:anywhere}
        .detail-modal-box .result-file-date{flex:0 0 auto;color:#718096;font-size:11px;white-space:nowrap}
        .detail-modal-box .planning-result-box form{display:flex!important;align-items:center!important;gap:9px!important;flex-wrap:wrap!important;margin:0!important}
        .detail-modal-box .planning-result-box form[hidden]{display:none!important}
        .detail-modal-box .planning-result-box form input[type=file]{flex:1 1 220px;min-height:36px;padding:6px 8px!important;border:1px solid #b8c9da!important;border-radius:8px!important;background:#fff!important;color:#627d98!important;font-size:12px!important}
        .detail-modal-box .planning-result-box form button{min-height:36px!important;padding:8px 12px!important;background:#111c91!important;color:#fff!important;border:0!important;border-radius:8px!important;font-size:12px!important;white-space:nowrap}
        .detail-modal-box .planning-result-box form button:hover{background:#0d166f!important}
        .detail-modal-box .result-waiting-message{padding:9px 10px;border:1px solid #f1d28a;border-radius:8px;background:#fffaf0;color:#8a6417;font-size:12px}
        @media(max-width:600px){.detail-modal-box .planning-result-box form{align-items:stretch!important;flex-direction:column!important}.detail-modal-box .planning-result-box form input[type=file],.detail-modal-box .planning-result-box form button{width:100%!important;flex-basis:auto}.detail-modal-box .result-file-row{align-items:flex-start}.detail-modal-box .result-file-info{align-items:flex-start;flex-direction:column;gap:3px}.detail-modal-box .result-file-date{white-space:normal}}
        .result-file-row { display:flex; align-items:center; justify-content:space-between; gap:10px; margin:4px 0; }
        .result-delete { padding:4px 8px; border:0; border-radius:4px; background:#a61b1b; color:#fff; cursor:pointer; font-size:12px; }
        .result-waiting-message { padding:10px; border-radius:6px; background:#fff5d6; color:#795900; }
        .proses-box { margin-top:18px; padding:12px; border:1px solid #c8d6e5; border-radius:8px; background:#f8fbff; }
        .proses-box h4 { margin:0 0 8px; color:#0b3d6b; }
        .proses-tabel { width:100%; border:1px solid #d6e2ee; border-radius:6px; overflow:hidden; font-size:12px; }
        .proses-tabel th, .proses-tabel td { padding:8px 10px; border-bottom:1px solid #e1e8ef; text-align:left; }
        .proses-tabel th { background:#eef3f8; color:#536477; }
        .proses-tabel tr:last-child td { border-bottom:0; }
        .proses-selesai { color:#159447; font-weight:600; }
        .proses-belum { color:#d33; font-weight:600; }
        /* Detail perluasan follows the same light, scroll-safe modal system as PB/PD. */
        #detailModalPerluasan{display:grid!important;place-items:center!important;overflow:hidden!important;padding:20px!important;background:rgba(7,20,69,.6)!important;backdrop-filter:blur(3px)}
        #detailModalPerluasan[hidden]{display:none!important}
        #detailModalPerluasan .detail-modal-box{display:flex!important;flex-direction:column!important;width:min(680px,100%)!important;max-height:calc(100vh - 40px)!important;min-height:0!important;padding:0!important;border:1px solid #dfe6fb!important;border-radius:24px!important;overflow:hidden!important;background:#fff!important;color:#202d5a!important;box-shadow:0 24px 64px rgba(15,35,104,.25)!important}
        #detailModalPerluasan .detail-modal-header{position:relative!important;display:flex!important;align-items:center!important;justify-content:space-between!important;gap:20px!important;padding:22px 24px 18px!important;border-bottom:1px solid #e0e7f7!important;background:#fff!important}
        #detailModalPerluasan .detail-modal-header h3{margin:0!important;padding:0 54px 0 0!important;border-radius:0!important;background:transparent!important;color:#1236a5!important;font-size:22px!important;font-weight:800!important;line-height:1.25!important}
        #detailModalPerluasan .detail-modal-summary{margin:5px 0 0!important;color:#60719b!important;font-size:14px!important;font-weight:500!important;line-height:1.45!important;overflow-wrap:anywhere}
        #detailModalPerluasan .detail-modal-close{position:absolute!important;top:18px!important;right:20px!important;z-index:2!important;display:grid!important;place-items:center!important;width:40px!important;height:40px!important;min-width:40px!important;min-height:40px!important;padding:0!important;border:0!important;border-radius:13px!important;background:#eef2ff!important;color:#394b7e!important;font-size:25px!important;font-family:Arial,sans-serif!important;font-weight:400!important;line-height:1!important;box-shadow:none!important;cursor:pointer!important}
        #detailModalPerluasan .detail-modal-close:hover{background:#dfe7ff!important;color:#173daf!important}
        #detailModalPerluasan .detail-modal-body{display:grid!important;gap:20px!important;min-height:0!important;overflow-y:auto!important;overscroll-behavior:contain!important;-webkit-overflow-scrolling:touch!important;padding:20px 24px!important}
        #detailModalPerluasan .detail-section{min-width:0!important}
        #detailModalPerluasan .detail-section-title{margin:0 0 10px!important;padding:0!important;border:0!important;background:transparent!important;color:#60719b!important;font-size:12px!important;font-weight:800!important;letter-spacing:.12em!important;line-height:1.4!important;text-transform:uppercase!important}
        #detailModalPerluasan .detail-customer-grid{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:10px!important;padding:0!important}
        #detailModalPerluasan .detail-grid>div{min-width:0!important;padding:13px 14px!important;border:1px solid #dfe6fb!important;border-radius:15px!important;background:#fff!important}
        #detailModalPerluasan .detail-grid small,#detailModalPerluasan .detail-cost-grid small{display:block!important;margin:0 0 5px!important;color:#60719b!important;font-size:11px!important;font-weight:700!important;letter-spacing:.045em!important;line-height:1.35!important;text-transform:uppercase!important}
        #detailModalPerluasan .detail-grid b,#detailModalPerluasan .detail-cost-grid b{display:block!important;color:#202d5a!important;font-size:14px!important;font-weight:700!important;line-height:1.45!important;overflow-wrap:anywhere!important}
        #detailModalPerluasan .detail-grid .detail-full{grid-column:1/-1!important}
        #detailModalPerluasan .detail-grid .detail-value-pill{display:inline-flex!important;max-width:100%!important;align-items:center!important;padding:5px 10px!important;border-radius:999px!important;background:#e8efff!important;color:#2147bd!important;font-size:12px!important;font-weight:700!important}
        #detailModalPerluasan .detail-cost-grid{display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:10px!important}
        #detailModalPerluasan .detail-cost-grid>div{min-width:0!important;padding:13px 14px!important;border:1px solid #dfe6fb!important;border-radius:15px!important;background:#f2f5ff!important}
        #detailModalPerluasan .detail-cost-grid b{color:#1236a5!important;font-size:17px!important}
        #detailModalPerluasan .detail-cost-grid .detail-rab-status{display:inline-flex!important;width:max-content!important;max-width:100%!important;align-items:center!important;padding:5px 10px!important;border-radius:999px!important;background:#fff1c9!important;color:#916000!important;font-size:12px!important;white-space:normal!important}
        #detailModalPerluasan .detail-cost-grid .detail-rab-status.is-filled{background:#dcf5e8!important;color:#176d43!important}
        #detailModalPerluasan .proses-list{overflow:hidden!important;border:1px solid #dfe6fb!important;border-radius:16px!important;background:#fff!important}
        #detailModalPerluasan .proses-row{display:grid!important;grid-template-columns:28px minmax(0,1fr) auto 104px!important;align-items:center!important;gap:12px!important;min-height:52px!important;padding:9px 14px!important;border-bottom:1px solid #e7ecf8!important;color:#26365e!important}
        #detailModalPerluasan .proses-row:last-child{border-bottom:0!important}
        #detailModalPerluasan .proses-icon{display:grid!important;place-items:center!important;width:24px!important;height:24px!important;border-radius:50%!important;background:#ffe5e5!important;color:#c12d26!important;font-size:16px!important;font-weight:800!important}
        #detailModalPerluasan .proses-row.is-complete .proses-icon{background:#dcf5e8!important;color:#176d43!important}
        #detailModalPerluasan .proses-label{min-width:0!important;font-size:13px!important;font-weight:600!important;line-height:1.4!important}
        #detailModalPerluasan .proses-status{display:inline-flex!important;justify-content:center!important;padding:5px 10px!important;border-radius:999px!important;font-size:11px!important;font-weight:700!important;line-height:1.35!important;white-space:nowrap!important}
        #detailModalPerluasan .proses-selesai{background:#dcf5e8!important;color:#176d43!important}
        #detailModalPerluasan .proses-belum{background:#ffe5e5!important;color:#bd2f27!important}
        #detailModalPerluasan .proses-row time{color:#60719b!important;font-size:12px!important;text-align:right!important;white-space:nowrap!important}
        #detailModalPerluasan .detail-technical-section{display:grid!important;gap:10px!important}
        #detailModalPerluasan #detailFormPerluasan{display:grid!important;gap:12px!important;margin:0!important}
        #detailModalPerluasan .detail-edit-table{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:12px 10px!important;margin:0!important}
        #detailModalPerluasan .detail-edit-table label{display:flex!important;flex-direction:column!important;gap:6px!important;min-width:0!important;color:#4f608b!important;font-size:12px!important;font-weight:700!important;line-height:1.35!important}
        #detailModalPerluasan .detail-edit-table .detail-field-rab{grid-column:1/-1!important}
        #detailModalPerluasan .detail-edit-table input,#detailModalPerluasan .detail-edit-table select{width:100%!important;min-width:0!important;min-height:44px!important;margin:0!important;padding:10px 13px!important;border:1px solid #d5def7!important;border-radius:13px!important;background:#f7f9ff!important;color:#26365e!important;font-size:14px!important;box-shadow:none!important}
        #detailModalPerluasan .detail-edit-table input:focus,#detailModalPerluasan .detail-edit-table select:focus{outline:0!important;border-color:#2451dc!important;box-shadow:0 0 0 3px rgba(36,81,220,.12)!important}
        #detailModalPerluasan #detailFormPerluasan>button[type="submit"]{justify-self:end!important;min-height:46px!important;padding:0 20px!important;border:0!important;border-radius:12px!important;background:#1941bd!important;color:#fff!important;font-size:14px!important;font-weight:800!important;box-shadow:0 8px 18px rgba(25,65,189,.2)!important}
        #detailModalPerluasan .detail-rab-form{display:grid!important;gap:12px!important;margin:0!important;padding:16px!important;border:1px solid #dfe6fb!important;border-radius:16px!important;background:#f7f9ff!important}
        #detailModalPerluasan .detail-rab-form label{display:flex!important;flex-direction:column!important;gap:6px!important;margin:0!important;color:#4f608b!important;font-size:12px!important;font-weight:700!important}
        #detailModalPerluasan .detail-rab-form input{width:100%!important;min-height:44px!important;padding:10px 13px!important;border:1px solid #d5def7!important;border-radius:13px!important;background:#f7f9ff!important;color:#26365e!important;font-size:14px!important}
        #detailModalPerluasan .detail-rab-form .btn{justify-self:end!important;min-height:46px!important;padding:0 20px!important;border:0!important;border-radius:12px!important;background:#1941bd!important;color:#fff!important;font-size:14px!important;font-weight:800!important;box-shadow:0 8px 18px rgba(25,65,189,.2)!important}
        #detailModalPerluasan .detail-rab-form .btn:hover,#detailModalPerluasan #detailFormPerluasan>button[type="submit"]:hover{background:#12349e!important}
        #detailModalPerluasan .detail-file-list{display:grid!important;gap:10px!important}
        #detailModalPerluasan .planning-result-box{display:grid!important;gap:10px!important;margin:0!important;padding:14px!important;border:1px solid #dfe6fb!important;border-radius:16px!important;background:#f7f9ff!important;box-shadow:none!important}
        #detailModalPerluasan .planning-result-box h4{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:12px!important;margin:0!important;padding:0 0 9px!important;border-bottom:1px solid #e1e8fa!important;color:#1236a5!important;font-size:14px!important;line-height:1.4!important}
        #detailModalPerluasan .file-status{flex:0 0 auto!important;padding:5px 10px!important;border-radius:999px!important;background:#ffe5e5!important;color:#bd2f27!important;font-size:11px!important;font-weight:700!important;white-space:nowrap!important}
        #detailModalPerluasan .file-status.is-complete{background:#dcf5e8!important;color:#176d43!important}
        #detailModalPerluasan .upload-owner-label{color:#60719b!important;font-size:11px!important}
        #detailModalPerluasan .planning-result-box>div[id$="Files"]{display:block!important;min-height:42px!important;padding:10px 12px!important;border:1px dashed #ccd8f7!important;border-radius:12px!important;background:#fff!important;color:#60719b!important;font-size:13px!important;line-height:1.45!important;overflow-wrap:anywhere!important}
        #detailModalPerluasan .result-empty{display:block!important;padding:1px 0!important;color:#60719b!important}
        #detailModalPerluasan .planning-result-box>div[id$="Files"] a{color:#2451dc!important;font-weight:700!important;text-decoration:underline!important;overflow-wrap:anywhere!important}
        #detailModalPerluasan .result-file-row{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:12px!important;margin:0!important;padding:4px 0!important}
        #detailModalPerluasan .result-file-row+.result-file-row{border-top:1px solid #edf1f8!important;padding-top:10px!important;margin-top:7px!important}
        #detailModalPerluasan .result-file-info{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:12px!important;min-width:0!important;flex:1!important}
        #detailModalPerluasan .result-file-info a{display:inline-flex!important;align-items:center!important;gap:9px!important;min-width:0!important;overflow-wrap:anywhere!important}
        #detailModalPerluasan .result-file-icon{display:grid!important;place-items:center!important;flex:0 0 30px!important;width:30px!important;height:30px!important;border-radius:9px!important;background:#e8efff!important;color:#2451dc!important}
        #detailModalPerluasan .result-file-date{flex:0 0 auto!important;color:#60719b!important;font-size:11px!important;white-space:nowrap!important}
        #detailModalPerluasan .result-delete{flex:0 0 auto!important;padding:5px 8px!important;border:0!important;border-radius:7px!important;background:#c23f3a!important;color:#fff!important;cursor:pointer!important;font-size:11px!important}
        #detailModalPerluasan .result-waiting-message{padding:9px 10px!important;border:1px solid #f1d28a!important;border-radius:9px!important;background:#fffaf0!important;color:#8a6417!important;font-size:12px!important}
        #detailModalPerluasan .planning-result-box form{display:flex!important;align-items:center!important;gap:9px!important;flex-wrap:wrap!important;margin:0!important}
        #detailModalPerluasan .planning-result-box form[hidden]{display:none!important}
        #detailModalPerluasan .planning-result-box form input[type=file]{flex:1 1 220px!important;min-width:0!important;min-height:38px!important;padding:6px 8px!important;border:1px solid #d5def7!important;border-radius:10px!important;background:#fff!important;color:#60719b!important;font-size:11px!important}
        #detailModalPerluasan .planning-result-box form input[type=file]::file-selector-button{margin-right:8px!important;padding:6px 9px!important;border:0!important;border-radius:7px!important;background:#eef2ff!important;color:#1236a5!important;font-weight:700!important;cursor:pointer!important}
        #detailModalPerluasan .planning-result-box form button{min-height:38px!important;padding:0 12px!important;border:1px solid #c8d5ff!important;border-radius:10px!important;background:#fff!important;color:#1236a5!important;font-size:12px!important;font-weight:700!important;white-space:nowrap!important;box-shadow:none!important}
        #detailModalPerluasan .planning-result-box form button:hover{background:#edf2ff!important}
        #detailModalPerluasan .detail-modal-footer{display:flex!important;justify-content:flex-end!important;padding:14px 24px!important;border-top:1px solid #e0e7f7!important;background:#fff!important}
        #detailModalPerluasan .detail-modal-dismiss{min-height:42px!important;padding:0 18px!important;border:1px solid #c8d5ff!important;border-radius:12px!important;background:#fff!important;color:#1236a5!important;font-size:14px!important;font-weight:700!important;box-shadow:none!important;cursor:pointer!important}
        #detailModalPerluasan .detail-modal-dismiss:hover{background:#f2f5ff!important}
        #detailModalPerluasan .vendor-form,#detailModalPerluasan .vendor-delete-card,#detailModalPerluasan .vendor-report-result,#detailModalPerluasan .construction-report-box{border-color:#dfe6fb!important;border-radius:15px!important;background:#f7f9ff!important}
        #detailModalPerluasan .vendor-form{display:grid!important;gap:10px!important;margin:0!important;padding:15px!important}
        #detailModalPerluasan .vendor-form label{display:grid!important;gap:6px!important;color:#4f608b!important;font-size:12px!important;font-weight:700!important}
        #detailModalPerluasan .vendor-form select,#detailModalPerluasan .vendor-form input[type=file]{min-height:42px!important;padding:9px 11px!important;border:1px solid #d5def7!important;border-radius:11px!important;background:#fff!important;color:#26365e!important}
        #detailModalPerluasan .vendor-delete-card{margin:0!important;padding:14px!important}
        #detailModalPerluasan .vendor-report-result{gap:9px!important;margin:0!important;padding:15px!important}
        #detailModalPerluasan .construction-report-box{display:grid!important;gap:10px!important;margin:0!important;padding:15px!important}
        #detailModalPerluasan .construction-report-box h4,#detailModalPerluasan .vendor-report-result h4,#detailModalPerluasan .vendor-form h4{margin:0!important;color:#1236a5!important}
        @media(max-width:700px){#detailModalPerluasan{padding:12px!important}#detailModalPerluasan .detail-modal-box{max-height:calc(100vh - 24px)!important;border-radius:20px!important}#detailModalPerluasan .detail-modal-header{padding:18px 18px 15px!important}#detailModalPerluasan .detail-modal-close{top:14px!important;right:14px!important}#detailModalPerluasan .detail-modal-body{gap:17px!important;padding:17px 18px!important}#detailModalPerluasan .detail-cost-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}#detailModalPerluasan .proses-row{grid-template-columns:26px minmax(0,1fr) auto!important;gap:8px!important;padding:9px 10px!important}#detailModalPerluasan .proses-row time{grid-column:2!important;text-align:left!important}#detailModalPerluasan .detail-modal-footer{padding:12px 18px!important}}
        @media(max-width:480px){#detailModalPerluasan .detail-modal-header h3{font-size:19px!important}#detailModalPerluasan .detail-modal-summary{max-width:calc(100% - 34px)!important;font-size:12px!important}#detailModalPerluasan .detail-customer-grid,#detailModalPerluasan .detail-edit-table{grid-template-columns:1fr!important}#detailModalPerluasan .detail-grid .detail-full,#detailModalPerluasan .detail-edit-table .detail-field-rab{grid-column:auto!important}#detailModalPerluasan .proses-row{grid-template-columns:24px minmax(0,1fr)!important}#detailModalPerluasan .proses-status,#detailModalPerluasan .proses-row time{grid-column:2!important;justify-self:start!important}#detailModalPerluasan .planning-result-box h4{align-items:flex-start!important;flex-direction:column!important;gap:6px!important}#detailModalPerluasan .result-file-row,#detailModalPerluasan .result-file-info{align-items:flex-start!important;flex-direction:column!important;gap:6px!important}#detailModalPerluasan .result-file-date{white-space:normal!important}#detailModalPerluasan .planning-result-box form{align-items:stretch!important;flex-direction:column!important}#detailModalPerluasan .planning-result-box form input[type=file],#detailModalPerluasan .planning-result-box form button{width:100%!important;flex-basis:auto!important}}
    </style>
    @endpush

