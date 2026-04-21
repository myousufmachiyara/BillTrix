@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      <header class="card-header">
        <h2 class="card-title">Settings</h2>
      </header>
      <div class="card-body">
        <div class="row">

          {{-- Left nav --}}
          <div class="col-md-3">
            <div class="list-group" id="settingsTabs">
              @php $settingsSections = [
                'branding'   => ['icon'=>'fa fa-palette',         'label'=>'Branding'],
                'regional'   => ['icon'=>'fa fa-globe',            'label'=>'Regional'],
                'financial'  => ['icon'=>'fa fa-dollar-sign',      'label'=>'Financial'],
                'modules'    => ['icon'=>'fa fa-puzzle-piece',     'label'=>'Module Toggles'],
                'approvals'  => ['icon'=>'fa fa-check-double',     'label'=>'Approvals'],
                'fbr'        => ['icon'=>'fa fa-file-contract',    'label'=>'FBR (Pakistan)'],
                'shopify'    => ['icon'=>'fab fa-shopify',         'label'=>'Shopify'],
                'whatsapp'   => ['icon'=>'fab fa-whatsapp',        'label'=>'WhatsApp / N8N'],
                'currencies' => ['icon'=>'fa fa-exchange-alt',     'label'=>'Currencies'],
                'email'      => ['icon'=>'fa fa-envelope',         'label'=>'Email / SMTP'],
              ]; @endphp
              @foreach($settingsSections as $key => $sec)
              <a href="#s-{{ $key }}" class="list-group-item list-group-item-action {{ $loop->first?'active':'' }}"
                 data-bs-toggle="list">
                <i class="{{ $sec['icon'] }} me-2"></i>{{ $sec['label'] }}
              </a>
              @endforeach
            </div>
          </div>

          {{-- Right content --}}
          <div class="col-md-9">
            <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
              @csrf @method('PUT')

              <div class="tab-content">

                {{-- Branding --}}
                <div class="tab-pane fade show active" id="s-branding">
                  <h5 class="mb-3 border-bottom pb-2">Branding</h5>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label>Company Name</label>
                      <input type="text" name="settings[company_name]" class="form-control"
                             value="{{ $settings['company_name']??'' }}">
                    </div>
                    <div class="col-md-3">
                      <label>Logo (Light)</label>
                      <input type="file" name="logo_light" class="form-control" accept=".png,.jpg,.svg,.webp">
                      @if(!empty($settings['logo_light']))
                        <img src="{{ asset('storage/'.$settings['logo_light']) }}" height="40" class="mt-2 border rounded p-1">
                      @endif
                    </div>
                    <div class="col-md-3">
                      <label>Logo (Dark)</label>
                      <input type="file" name="logo_dark" class="form-control" accept=".png,.jpg,.svg,.webp">
                      @if(!empty($settings['logo_dark']))
                        <img src="{{ asset('storage/'.$settings['logo_dark']) }}" height="40" class="mt-2 border rounded p-1">
                      @endif
                    </div>
                    <div class="col-md-2">
                      <label>Favicon</label>
                      <input type="file" name="favicon" class="form-control" accept=".png,.ico">
                    </div>
                    <div class="col-md-2">
                      <label>Primary Color</label>
                      <input type="color" name="settings[primary_color]" class="form-control form-control-color"
                             value="{{ $settings['primary_color']??'#0d6efd' }}">
                    </div>
                    <div class="col-md-4">
                      <label>Address</label>
                      <textarea name="settings[company_address]" class="form-control" rows="2">{{ $settings['company_address']??'' }}</textarea>
                    </div>
                    <div class="col-md-2">
                      <label>Phone</label>
                      <input type="text" name="settings[company_phone]" class="form-control"
                             value="{{ $settings['company_phone']??'' }}">
                    </div>
                    <div class="col-md-2">
                      <label>Email</label>
                      <input type="email" name="settings[company_email]" class="form-control"
                             value="{{ $settings['company_email']??'' }}">
                    </div>
                  </div>
                </div>

                {{-- Regional --}}
                <div class="tab-pane fade" id="s-regional">
                  <h5 class="mb-3 border-bottom pb-2">Regional Settings</h5>
                  <div class="row g-3">
                    <div class="col-md-4">
                      <label>Default Currency</label>
                      <select name="settings[default_currency]" class="form-control select2-js">
                        @foreach($currencies ?? [] as $cur)
                        <option value="{{ $cur->code }}" {{ ($settings['default_currency']??'PKR')==$cur->code?'selected':'' }}>
                          {{ $cur->code }} — {{ $cur->name }}
                        </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label>Date Format</label>
                      <select name="settings[date_format]" class="form-control">
                        @foreach(['Y-m-d'=>'YYYY-MM-DD','d/m/Y'=>'DD/MM/YYYY','m/d/Y'=>'MM/DD/YYYY','d-m-Y'=>'DD-MM-YYYY'] as $fmt=>$lbl)
                        <option value="{{ $fmt }}" {{ ($settings['date_format']??'Y-m-d')==$fmt?'selected':'' }}>{{ $lbl }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label>Timezone</label>
                      <select name="settings[timezone]" class="form-control select2-js">
                        @foreach(timezone_identifiers_list() as $tz)
                        <option value="{{ $tz }}" {{ ($settings['timezone']??'Asia/Karachi')==$tz?'selected':'' }}>{{ $tz }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>Decimal Separator</label>
                      <select name="settings[decimal_separator]" class="form-control">
                        <option value="." {{ ($settings['decimal_separator']??'.')==='.'?'selected':'' }}>. (Period)</option>
                        <option value="," {{ ($settings['decimal_separator']??'.') === ','?'selected':'' }}>, (Comma)</option>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>Thousands Separator</label>
                      <select name="settings[thousands_separator]" class="form-control">
                        <option value="," {{ ($settings['thousands_separator']??',')==','?'selected':'' }}>, (Comma)</option>
                        <option value="." {{ ($settings['thousands_separator']??',')==='.'?'selected':'' }}>. (Period)</option>
                      </select>
                    </div>
                  </div>
                </div>

                {{-- Financial --}}
                <div class="tab-pane fade" id="s-financial">
                  <h5 class="mb-3 border-bottom pb-2">Financial Settings</h5>
                  <div class="row g-3">
                    <div class="col-md-3">
                      <label>Financial Year Start (MM-DD)</label>
                      <input type="text" name="settings[fy_start]" class="form-control"
                             placeholder="07-01" value="{{ $settings['fy_start']??'07-01' }}">
                    </div>
                    <div class="col-md-3">
                      <label>Default Tax %</label>
                      <input type="number" step="any" name="settings[default_tax]" class="form-control"
                             value="{{ $settings['default_tax']??0 }}">
                    </div>
                    <div class="col-md-2">
                      <label>Invoice Prefix</label>
                      <input type="text" name="settings[invoice_prefix]" class="form-control"
                             value="{{ $settings['invoice_prefix']??'INV-' }}">
                    </div>
                    <div class="col-md-2">
                      <label>PO Prefix</label>
                      <input type="text" name="settings[po_prefix]" class="form-control"
                             value="{{ $settings['po_prefix']??'PO-' }}">
                    </div>
                    <div class="col-md-2">
                      <label>GRN Prefix</label>
                      <input type="text" name="settings[grn_prefix]" class="form-control"
                             value="{{ $settings['grn_prefix']??'GRN-' }}">
                    </div>
                    <div class="col-md-4">
                      <label>Stock Valuation Method</label>
                      <select name="settings[stock_valuation]" class="form-control">
                        <option value="avg"  {{ ($settings['stock_valuation']??'avg')==='avg' ?'selected':'' }}>Weighted Average</option>
                        <option value="fifo" {{ ($settings['stock_valuation']??'avg')==='fifo'?'selected':'' }}>FIFO</option>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>Default Credit Days</label>
                      <input type="number" name="settings[default_credit_days]" class="form-control"
                             value="{{ $settings['default_credit_days']??30 }}">
                    </div>
                  </div>
                </div>

                {{-- Module Toggles --}}
                <div class="tab-pane fade" id="s-modules">
                  <h5 class="mb-3 border-bottom pb-2">Module Toggles</h5>
                  <div class="row g-3">
                    @foreach([
                      'module_production' => 'Production Module',
                      'module_pos'        => 'POS System',
                      'module_projects'   => 'Projects & Services',
                      'module_tasks'      => 'Task Management',
                      'module_pdc'        => 'Post-Dated Cheques',
                      'module_fbr'        => 'FBR Invoicing',
                      'module_shopify'    => 'Shopify Integration',
                      'module_ai'         => 'AI Accountant',
                    ] as $key => $label)
                    <div class="col-md-6">
                      <div class="card p-3 d-flex flex-row align-items-center justify-content-between">
                        <label class="fw-semibold mb-0" for="{{ $key }}">{{ $label }}</label>
                        <div class="form-check form-switch mb-0 ms-3">
                          <input type="checkbox" name="settings[{{ $key }}]" value="1"
                                 class="form-check-input" role="switch" id="{{ $key }}"
                                 {{ !empty($settings[$key])?'checked':'' }}>
                        </div>
                      </div>
                    </div>
                    @endforeach
                  </div>
                </div>

                {{-- FBR --}}
                <div class="tab-pane fade" id="s-fbr">
                  <h5 class="mb-3 border-bottom pb-2">FBR Pakistan Integration</h5>
                  <div class="row g-3">
                    <div class="col-md-4">
                      <label>NTN Number</label>
                      <input type="text" name="settings[fbr_ntn]" class="form-control"
                             value="{{ $settings['fbr_ntn']??'' }}">
                    </div>
                    <div class="col-md-4">
                      <label>FBR PRAL User ID</label>
                      <input type="text" name="settings[fbr_user_id]" class="form-control"
                             value="{{ $settings['fbr_user_id']??'' }}">
                    </div>
                    <div class="col-md-4">
                      <label>FBR PRAL Password</label>
                      <input type="password" name="settings[fbr_password]" class="form-control"
                             placeholder="Leave blank to keep current">
                    </div>
                    <div class="col-12">
                      <div class="form-check">
                        <input type="checkbox" name="settings[fbr_enabled]" value="1"
                               class="form-check-input" id="fbrEnabled"
                               {{ !empty($settings['fbr_enabled'])?'checked':'' }}>
                        <label class="form-check-label" for="fbrEnabled">Enable FBR Invoice Submission</label>
                      </div>
                    </div>
                  </div>
                </div>

                {{-- WhatsApp / N8N --}}
                <div class="tab-pane fade" id="s-whatsapp">
                  <h5 class="mb-3 border-bottom pb-2">WhatsApp &amp; N8N Automation</h5>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label>N8N Webhook Base URL</label>
                      <input type="url" name="settings[n8n_webhook_url]" class="form-control"
                             placeholder="https://your-n8n.example.com/webhook/…"
                             value="{{ $settings['n8n_webhook_url']??'' }}">
                    </div>
                    <div class="col-md-6">
                      <label>WhatsApp API Key</label>
                      <input type="password" name="settings[whatsapp_api_key]" class="form-control"
                             placeholder="Leave blank to keep current">
                    </div>
                    <div class="col-12">
                      <label class="fw-semibold">Automation Triggers</label>
                      <div class="row mt-2">
                        @foreach([
                          'wa_on_invoice'  => 'Send invoice on creation',
                          'wa_on_payment'  => 'Send payment confirmation',
                          'wa_overdue'     => 'Overdue reminders',
                          'wa_pdc_alert'   => 'PDC maturity alert',
                          'wa_low_stock'   => 'Low stock alert',
                          'wa_pos_receipt' => 'POS receipt via WhatsApp',
                        ] as $k => $lbl)
                        <div class="col-md-4">
                          <div class="form-check">
                            <input type="checkbox" name="settings[{{ $k }}]" value="1"
                                   class="form-check-input" id="{{ $k }}"
                                   {{ !empty($settings[$k])?'checked':'' }}>
                            <label class="form-check-label small" for="{{ $k }}">{{ $lbl }}</label>
                          </div>
                        </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                </div>

                {{-- Shopify --}}
                <div class="tab-pane fade" id="s-shopify">
                  <h5 class="mb-3 border-bottom pb-2">Shopify Integration</h5>
                  <div class="row g-3">
                    <div class="col-md-5">
                      <label>Shop URL</label>
                      <input type="text" name="settings[shopify_shop_url]" class="form-control"
                             placeholder="yourstore.myshopify.com"
                             value="{{ $settings['shopify_shop_url']??'' }}">
                    </div>
                    <div class="col-md-5">
                      <label>API Access Token</label>
                      <input type="password" name="settings[shopify_access_token]" class="form-control"
                             placeholder="Leave blank to keep current">
                    </div>
                    <div class="col-12">
                      <div class="form-check">
                        <input type="checkbox" name="settings[shopify_enabled]" value="1"
                               class="form-check-input" id="shopifyEnabled"
                               {{ !empty($settings['shopify_enabled'])?'checked':'' }}>
                        <label class="form-check-label" for="shopifyEnabled">Enable Shopify Sync</label>
                      </div>
                    </div>
                  </div>
                </div>

              </div><!-- /.tab-content -->

              <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save"></i> Save Settings
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>
@endsection
