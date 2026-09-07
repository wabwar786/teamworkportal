@extends('layouts.portal')
@section('title','Vault')
@section('content')
<div class="sechead"><h2>Vault</h2><p>Saved passwords, links and notes live inside each person's daily log under "Saved".</p></div>
<div class="panel"><div class="panel-body">
  <p style="font-size:13px;color:var(--ink-2);line-height:1.7">
    Every team member has a <b>Saved</b> block on their day screen for passwords, links and notes. Those entries are kept with the log and preserved even after an employee is archived. Open <a href="{{ route('portal.log') }}" style="color:var(--green)">Full log</a> or an archived employee's log to review them.</p>
</div></div>
@endsection
