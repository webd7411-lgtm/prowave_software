<?php
namespace App\Http\Requests\Crm;
use App\Models\LeadStatus;
use Illuminate\Foundation\Http\FormRequest; use Illuminate\Validation\Rule;
class LeadRequest extends FormRequest {
 public function authorize():bool{return $this->user()->can('leads.create')||$this->user()->can('leads.edit');}
 public function rules():array{
  $statuses=LeadStatus::pluck('slug')->all();
  $rules=['company_name'=>['required','string','max:255'],'contact_person'=>['nullable','string','max:255'],'phone'=>['required','string','max:50'],'whatsapp'=>['nullable','string','max:50'],'email'=>['nullable','email','max:255'],'website'=>['nullable','url','max:255'],'address'=>['nullable','string','max:2000'],'city'=>['nullable','string','max:100'],'country'=>['nullable','string','max:100'],'source'=>['nullable',Rule::in(['website','facebook','instagram','whatsapp','referral','fiverr','upwork','google','linkedin','direct','other'])],'service_interested'=>['nullable','string','max:255'],'estimated_budget'=>['nullable','numeric','min:0'],'owner_id'=>['nullable','exists:users,id'],'status'=>['required',Rule::in($statuses)],'priority'=>['nullable',Rule::in(['low','medium','high','urgent'])],'expected_closing_date'=>['nullable','date'],'notes'=>['nullable','string','max:5000'],'follow_up_date'=>['nullable','date'],'follow_up_time'=>['nullable','date_format:H:i']];
  if($this->input('status')==='meeting_scheduled'){ $rules['follow_up_date']=['required','date','after_or_equal:today']; $rules['follow_up_time']=['required','date_format:H:i']; }
  return $rules;
 } }