<?php

namespace App\Libraries;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SalesTransaction;

class IndoitMenuLib {
    private $htmlModulePosition = "";
    private $module = [];

    public function generateModulePosition($position,$module,$interation=0,$parent=true)
    {
        if(empty($position)){
            return;
        }

        if($parent==true){
        $this->htmlModulePosition .= '
            <ol class="dd-list">
            ';
        }

        foreach($position as $row){
            if(empty($module[$row->id])){
                if(!empty($row->children)){
                    $this->generateModulePosition($row->children,$module,1,false);
                    continue;
                }
                continue;
            }else{
                $this->module[$row->id] = $module[$row->id];
            }

            $name = $module[$row->id]->name;
            $url = $module[$row->id]->url;

            if($url==""){
                $url = '#';
            }

            $this->htmlModulePosition .= '
            <li class="dd-item" data-id="'.$row->id.'">
                    <div class="dd-handle">'.$name.' <span class="label badge bg-info">(url:'.$url.'/)</span></div>
            ';

            if(!empty($row->children)){
                $this->generateModulePosition($row->children,$module,$interation+1);
            }

            $this->htmlModulePosition .= '
            </li>
            ';
        }

        if($interation==0){
            foreach($module as $row){
                if(empty($this->module[$row->id])){
                    $this->htmlModulePosition .= '
                    <li class="dd-item" data-id="'.$row->id.'">
                    <div class="dd-handle">'.$row->name.' <span class="label badge bg-info">(url:'.$row->url.'/)</span></div>
                    </li>
                    ';
                }
            }
            
        }

        if($parent==true){
        $this->htmlModulePosition .= '
            </ol>
        ';
        }

        return  $this->htmlModulePosition;
    }

    private $htmlSidebar = "";
    private $module_sidebar = [];
    private $idx = -1;
    private $x = -1;    
    public function generateSidebarMenu($position,$module_sidebar,$module_sidebar_idx,$active_menu,$cidx,$exc,$interation=0,$parent=true)
    {                     
        $class ='';           
        if(empty($position)){            
            return;
        }
        $this->x++;
                
        if($parent==true){                        
            if ($active_menu) {                
                if (in_array($cidx[$this->idx],$active_menu)) {
                    $this->htmlSidebar .= '
                                <div class="menu-dropdown collapse show" id="sidebar'.$module_sidebar[$cidx[$this->idx]]->name.'">
                                <ul class="nav nav-sm flex-column">
                            ';
                }else {
                    $this->htmlSidebar .= '
                                <div class="menu-dropdown collapse " id="sidebar'.$module_sidebar[$cidx[$this->idx]]->name.'">
                                <ul class="nav nav-sm flex-column">
                            ';
                }
                
            } else {
                $this->htmlSidebar .= '
                                    <div class="menu-dropdown collapse " id="sidebar'.$module_sidebar[$cidx[$this->idx]]->name.'">
                                    <ul class="nav nav-sm flex-column">
                                ';
            }                    
        }

        foreach($position as $row){
            if(empty($module_sidebar[$row->id])){
                if(!empty($row->children)){                    
                    $this->generateSidebarMenu($row->children,$module_sidebar,$module_sidebar_idx,$active_menu,$cidx,$exc,$interation+1,false);
                    continue;
                }
                continue; 
            }else{                
                $this->module_sidebar[$row->id] = $module_sidebar[$row->id];
            }

            $icon = $module_sidebar[$row->id]->icon;
            $name = $module_sidebar[$row->id]->name;
            $url = $module_sidebar[$row->id]->url;
            $module_id = $module_sidebar[$row->id]->id;

            if($url=="#"){
                $url = '#sidebar'.$name;
            }

            $class ='';
            $addComponent = '';
            if(!empty($row->children)){                                  
                // $class = 'menu-link';
                $addComponent = 'data-bs-toggle="collapse"';
            }else {                
                // $class = 'menu-link';
                $addComponent = '';
            }
            if (in_array($row->id,$exc)) {
                $class = '';
            }else {
                $class = 'menu-link';
            }

            $actual_url = \Request::segment(1);
            if($url==$actual_url){
                $class .=" active ";
            }

            foreach($active_menu as $value){
                if($module_id==$value){
                    $class .=" active ";
                } 
            }
            // if (strtolower($name) == 'dashboard') {
                
            // }else {
            //     $this->htmlSidebar .= '<li class="nav-item">
            //                         <a class="nav-link '.$class.'" href="'.url($url).'" '.$addComponent.' role="button" aria-expanded="false" aria-controls="sidebar'.$name.'">
            //                             <i class="'.$icon.'"></i> <span>'.$name.'</span>';                                        
            // }            
            // Badge notifikasi untuk menu tertentu
            $badgeHtml = '';
            if($module_sidebar[$row->id]->url === 'ecommerce-transaction'){
                $unpaidCount = SalesTransaction::whereNull('deleted_at')
                    ->where('channel','ecommerce')
                    ->where('payment_status','pending')
                    ->count();
                if($unpaidCount > 0){
                    $badgeHtml = ' <span class="badge badge-pill bg-danger" style="font-size:10px;">'.$unpaidCount.'</span>';
                }

                // Peringatan Xendit Dorman (76 hari = H-14 dari batas 90 hari)
                $lastTrx = SalesTransaction::where('channel', 'ecommerce')->orderBy('created_at', 'desc')->value('created_at');
                if ($lastTrx) {
                    $daysSinceLastTrx = (int) \Carbon\Carbon::parse($lastTrx)->startOfDay()->diffInDays(\Carbon\Carbon::now()->startOfDay());
                    if ($daysSinceLastTrx >= 76) {
                        $sisaHari = 90 - $daysSinceLastTrx;
                        $badgeHtml .= ' <span class="badge badge-pill bg-warning text-dark" style="font-size:10px;" title="Xendit hampir Dorman! (H-'.$sisaHari.')">⚠️ Xendit</span>';
                    }
                }
            }

            $this->htmlSidebar .= '<li class="nav-item">
                                <a class="nav-link '.$class.'" href="'.url($url).'" '.$addComponent.' role="button" aria-expanded="false" aria-controls="sidebar'.$name.'">
                                    <i class="'.$icon.'"></i> <span>'.$name.'</span>'.$badgeHtml;                                        

            if(!empty($row->children)){
                $this->htmlSidebar .= '                    
                    </a>
                ';                
                $this->idx++;
                $this->generateSidebarMenu($row->children,$module_sidebar,$module_sidebar_idx,$active_menu,$cidx,$exc,$interation+1,true);
            }else{
                $this->htmlSidebar .= '</a>';
            }

            $this->htmlSidebar .= '</li>';
        }
        
        if($interation==0){            
            foreach($module_sidebar as $row){                
                if(empty($this->module_sidebar[$row->id])){                    
                    $this->htmlSidebar .= '
                    <li class="nav-item">
                        <a class="nav-link "'.$class.' href="'.url($row->url).'" role="button" aria-expanded="false" aria-controls="sidebarPages">
                            <i class="'.$row->icon.'"></i> <span>'.$row->name.'</span></li></a>';
                }
            }
        }

        if($parent==true){
            $this->htmlSidebar .= '
                </ul></div>
            ';
        }

        return  $this->htmlSidebar;
    }

    private $parent_arr = [];
    public function get_array_parent($d=array(),$parent = 0){
        $iterator = new \RecursiveArrayIterator($d);
        while($iterator->valid()){
            if($iterator->hasChildren()){
                $last_parent = 0;
                foreach ($iterator->getChildren() as $key => $value) {
                    if(is_array($value)){
                        $this->get_array_parent($value,$last_parent);
                    }else{
                        $last_parent = $value;
                        $this->parent_arr[$value] = $parent;
                    }
                }
            } else {
                echo "No children.\n";
            }
    
            $iterator->next();
        }
        return $this->parent_arr;
    }

    private $active_menu = [];
    public function get_active_menu($parent_arr,$modules_id){
       if(!empty($parent_arr[$modules_id])){
            $this->active_menu[] = $parent_arr[$modules_id];
            $this->get_active_menu($parent_arr,$parent_arr[$modules_id]);
        }
        return $this->active_menu;
    }
    
}