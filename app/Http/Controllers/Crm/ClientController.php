<?php
namespace App\Http\Controllers\Crm;
use App\Http\Controllers\Controller; use App\Http\Requests\Crm\ClientRequest; use App\Models\{Client,User}; use App\Services\NotificationService; use Illuminate\Http\{Request,RedirectResponse}; use Illuminate\View\View;
class ClientController extends Controller {
 public function index(Request $r):View{$q=Client::with('manager')->latest();if($r->filled('search'))$q->where(fn($x)=>$x->where('company_name','like','%'.$r->search.'%')->orWhere('email','like','%'.$r->search.'%')->orWhere('phone','like','%'.$r->search.'%'));return view('crm.clients.index',['clients'=>$q->paginate(15)->withQueryString()]);}
 public function create():View{return view('crm.clients.form',['client'=>new Client,'users'=>User::orderBy('name')->get(),'action'=>route('crm.clients.store')]);}
 public function store(ClientRequest $r):RedirectResponse{$client=Client::create($r->validated()+['client_number'=>'CL-'.now()->format('ymd').'-'.str_pad((string)(Client::withTrashed()->count()+1),4,'0',STR_PAD_LEFT),'created_by'=>auth()->id(),'updated_by'=>auth()->id()]);app(NotificationService::class)->sendToPermission('clients.view','New client added',$client->company_name,['category'=>'client','url'=>route('crm.clients.show',$client),'color'=>'success']);return redirect()->route('crm.clients.show',$client)->with('toast',['type'=>'success','message'=>'Client created successfully.']);}
 public function show(Client $client):View{$client->load(['manager','invoices','payments','quotations','ledger']);return view('crm.clients.show',compact('client'));}
 public function edit(Client $client):View{return view('crm.clients.form',['client'=>$client,'users'=>User::orderBy('name')->get(),'action'=>route('crm.clients.update',$client)]);}
 public function update(ClientRequest $r,Client $client):RedirectResponse{$client->update($r->validated()+['updated_by'=>auth()->id()]);return redirect()->route('crm.clients.show',$client)->with('toast',['type'=>'success','message'=>'Client updated.']);}
 public function destroy(Client $client):RedirectResponse{$client->delete();return redirect()->route('crm.clients.index')->with('toast',['type'=>'success','message'=>'Client deleted.']);}
}
