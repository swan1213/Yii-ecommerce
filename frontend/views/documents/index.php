<?php

use common\models\User;
use yii\helpers\Html;
use yii\grid\GridView;
use common\models\DocumentFile;
use common\models\CorporateDocument;

use yii\widgets\Breadcrumbs;
use common\models\Connection;



$this->title = 'Corporate Documents';
$this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = $this->title;

$addMoreClass	=   empty($document_director) ? 'addMoreDirectors' : 'updateMoreDirectors';
$addMoreText	=   empty($document_director) ? '+Add More' : '+Add More';

$currentUserID = Yii::$app->user->identity->id;
?>

<div class="page-head">
    <h2 class="page-head-title"><?= Html::encode($this->title) ?></h2>
    <ol class="breadcrumb page-head-nav">
        <?php
        echo Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]);
        ?>
    </ol>
</div>
<div class="row">
    <!--Default Tabs-->
    <div class="col-sm-12">

        <div class="panel panel-default">
            <div class="tab-container">

                <ul class="nav nav-tabs">
                    <li class="active customCC"><a href="#banking" data-toggle="tab">Banking</a></li>
                    <li class="customCC"><a href="#business" data-toggle="tab">Business</a></li>
                    <li class="customCC"><a href="#directors" data-toggle="tab">Directors</a></li>
                    <li class="customCC"><a href="#payments" data-toggle="tab">Payments</a></li>
                </ul>
                <div class="tab-content product_details">
                    <div id="banking" class="tab-pane active cont">
                        <div class="table-responsive">
                            <table id="general_tbl" style="clear: both" class="table table-striped table-borderless">
                                <tbody>
                                    <tr>
                                        <td width="35%">Account No.</td>
                                        <td width="65%"><a class="documents_fields" id="account_no" href="#" data-type="text" data-title="Please Enter value"><?=$model['bank_account_no'];?></a></td>
                                    </tr>
                                    <tr>
                                        <td width="35%">Routing No.</td>
                                        <td width="65%"><a class="documents_fields"  id="routing_no" href="#" data-type="text" data-title="Please Enter value" ><?=$model['bank_roting_no'];?></a></td>
                                    </tr>
                                    <tr>
                                        <td width="35%">Bank Code</td>
                                        <td width="65%"><a class="documents_fields" id="bank_code" href="#" data-type="text" data-title="Please Enter value" ><?=$model['bank_code'];?></a></td>
                                    </tr>
                                    <tr>
                                        <td width="35%">Bank Name</td>
                                        <td width="65%"><a class="documents_fields" id="bank_name"  href="#" data-type="text" data-title="Please Enter value"><?=$model['bank_name'];?></a></td>
                                    </tr>
                                    <tr>
                                        <td width="35%">Bank Address</td>
                                        <td width="65%"><a class="documents_fields"  id="bank_address"  href="#" data-type="textarea" data-title="Please Enter value"><?=$model['bank_address'];?></a></td>
                                    </tr>
                                    <tr>
                                        <td width="35%">SWIFT</td>
                                        <td width="65%"><a class="documents_fields"  id="swift"  href="#" data-type="text" data-title="Please Enter value "><?=$model['bank_swift'];?></a></td>
                                    </tr>
                                    <tr>
                                        <td width="35%">Account Type</td>
                                        <td width="65%"><a class="documents_fields_account_type" data-select-val="<?=$model['bank_account_type'];?>" id="account_type"  href="#" data-type="select" data-title="Please Enter value (must be unique)"><?=$model['bank_account_type'];?></a></td>
                                    </tr>
                                    <tr>
                                        <td width="35%">Latest Bank Statement</td>
                                        <td width="65%"></td>
                                    </tr>
                                </tbody>
                            </table>

			    <?php 
				$banking_file_data = DocumentFile::findAll(['user_id'=> $currentUserID, 'type'=>DocumentFile::DOCUMENT_TYPE_BANKING]);
				//echo "<pre>"; print_r($banking_file_data); echo "</pre>";
				
				if(empty($banking_file_data)){ ?>
				    <div class="main-content container-fluid">
					<form id="bankingform" action="" class="dropzone">
					    <div class="dz-message">
						<div class="icon"><span class="mdi mdi-cloud-upload"></span></div>
						<h2>Drag and Drop files here</h2>
	<!--                                        <span class="note">(This is just a demo dropzone. Selected files are <strong>not</strong> actually uploaded.)</span>-->
					    </div>
					</form>
				    </div>
			    <?php } 
			    else{
				foreach($banking_file_data as $_banking){
				    $file = $_banking->file_path;
				    $banking_doc_id = $_banking->id;
				    $ext = pathinfo($file);
				    $file_ext = $ext['extension'];
				    //echo "<pre>"; print_r($ext); die('End here');
				    $file_size = number_format(filesize($file)/1024, 1);
				    $ext_array = array('png', 'img', 'gif', 'svg', 'jpeg', 'jpg');
				    if(in_array($file_ext, $ext_array)){ ?>
					<div class="dropzone dotted_border">
					    <div class="dz-preview dz-error dz-complete dz-image-preview">
						<div class="dz-image"><img width="100%" alt="<?php echo $ext['basename']; ?>" src="<?php echo $file; ?>"></div>
						<div class="dz-error-mark custom_error_css delete_files" data-imgattr="<?=$file;?>" data-bank_doc_id="<?=$banking_doc_id;?>" data-directory-type="banking">
						    <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
							<title>Error</title> 
							<defs></defs>
							<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
							    <g id="Check-+-Oval-2" sketch:type="MSLayerGroup" stroke="#747474" stroke-opacity="0.198794158" fill="#FFFFFF" fill-opacity="0.816519475"> 
								<path d="M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" id="Oval-2" sketch:type="MSShapeGroup">
								</path>        
							    </g>      
							</g>   .
						    </svg>  
						</div>
						<div class="dz-details">
						    <div class="dz-size">
							<span data-dz-size=""><strong><?php echo $file_size; ?></strong> KB</span>
						    </div>    
						    <div class="dz-filename"><span data-dz-name=""><?php echo $ext['basename']; ?></span></div>  
						</div>
						<div class="download-mark">
						    <div class="icon"><a href="<?=$file;?>" download><span class="mdi mdi-download"></span></a></div>
						</div>
						
					    </div>
					</div>
				<?php   }
					else{ ?>
					<div class="dropzone dotted_border">
					    <div class="dz-preview dz-file-preview dz-processing dz-error dz-complete dz-image-preview">
						<div class="dz-image"><img data-dz-thumbnail=""></div>
						<div class="dz-error-mark custom_error_css delete_files" data-imgattr="<?=$file;?>" data-bank_doc_id="<?=$banking_doc_id;?>" data-directory-type="banking">
						    <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
							<title>Error</title> 
							<defs></defs>
							<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
							    <g id="Check-+-Oval-2" sketch:type="MSLayerGroup" stroke="#747474" stroke-opacity="0.198794158" fill="#FFFFFF" fill-opacity="0.816519475"> 
								<path d="M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" id="Oval-2" sketch:type="MSShapeGroup">
								</path>        
							    </g>      
							</g>   .
						    </svg>  
						</div>
						<div class="dz-details">
						    <div class="dz-size">
							<span data-dz-size=""><strong><?php echo $file_size; ?></strong> KB</span>
						    </div>    
						    <div class="dz-filename"><span data-dz-name=""><?php echo $ext['basename']; ?></span></div>  
						</div>
						<div class="download-mark">
						    <div class="icon"><a href="<?=$file;?>" download><span class="mdi mdi-download"></span></a></div>
						</div>
<!--						<div class="dz-success-mark">
						    <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">      
						    <title>Check</title><defs></defs>
						    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
						    <path d="M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" id="Oval-2" stroke-opacity="0.198794158" stroke="#747474" fill-opacity="0.816519475" fill="#FFFFFF" sketch:type="MSShapeGroup"></path>
						    </g>    
						    </svg> 
						</div>-->
					    </div>
					    </div>
				<?php	}
				}
			    } ?>
			</div>
		    </div>
                    <div id="business" class="tab-pane cont">
                        <div class="table-responsive">
                            <input type="hidden" id="gcatname" value="" />
                            <table id="attr_tbl" style="clear: both" class="table table-striped table-borderless">
                                <tbody>
                                    <tr>
                                        <td width="35%">Tax ID</td>
                                        <td width="65%"><a class="documents_fields" id="tax_id"  href="#" data-type="text" data-title="Please Enter value"><?=$model['business_tax_id'];?></a></td>
                                    </tr>
                                    <tr>
                                        <td width="35%">Articles of Incorporation</td>
                                        <td width="65%"></td>
                                    </tr>

                                </tbody>
                            </table>
			    <?php
			    $buisness_file_data = DocumentFile::findAll(['user_id'=> $currentUserID, 'type'=>DocumentFile::DOCUMENT_TYPE_BUSINESS]);
			    if(empty($buisness_file_data)){ 
			    ?>
				<div class="main-content container-fluid">
				    <form id="businessform" action="" class="dropzone ">
					<div class="dz-message">
					    <div class="icon"><span class="mdi mdi-cloud-upload"></span></div>
					    <h2>Drag and Drop files here</h2>
    <!--                                        <span class="note">(This is just a demo dropzone. Selected files are <strong>not</strong> actually uploaded.)</span>-->
					</div>
				    </form>
				</div>
			    <?php } else{ 
			    foreach($buisness_file_data as $_buisness){
				    $buisness_file = $_buisness->file_path;
				    $buisness_doc_id = $_buisness->id;
				    $ext = pathinfo($buisness_file);
				    $file_ext = $ext['extension'];
				    //echo "<pre>"; print_r($ext); die('End here');
				    $file_size = number_format(filesize($buisness_file)/1024, 1);
				    $ext_array = array('png', 'img', 'gif', 'svg', 'jpeg', 'jpg');
				    if(in_array($file_ext, $ext_array)){ ?>
					<div class="dropzone dotted_border">
					    <div class="dz-preview dz-error dz-complete dz-image-preview">
						<div class="dz-image"><img width="100%" alt="<?php echo $ext['basename']; ?>" src="<?php echo $buisness_file; ?>"></div>
						<div class="dz-error-mark custom_error_css delete_files" data-imgattr="<?=$buisness_file;?>" data-bank_doc_id="<?=$buisness_doc_id;?>" data-directory-type="business">
						    <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
							<title>Error</title> 
							<defs></defs>
							<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
							    <g id="Check-+-Oval-2" sketch:type="MSLayerGroup" stroke="#747474" stroke-opacity="0.198794158" fill="#FFFFFF" fill-opacity="0.816519475"> 
								<path d="M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" id="Oval-2" sketch:type="MSShapeGroup">
								</path>        
							    </g>      
							</g>   .
						    </svg>  
						</div>
						<div class="dz-details">
						    <div class="dz-size">
							<span data-dz-size=""><strong><?php echo $file_size; ?></strong> KB</span>
						    </div>    
						    <div class="dz-filename"><span data-dz-name=""><?php echo $ext['basename']; ?></span></div>  
						</div>
						<div class="download-mark">
						    <div class="icon"><a href="<?=$buisness_file;?>" download><span class="mdi mdi-download"></span></a></div>
						</div>
					    </div>
					</div>
				<?php   }
					else{ ?>
					<div class="dropzone dotted_border">
					    <div class="dz-preview dz-file-preview dz-processing dz-error dz-complete dz-image-preview">
						<div class="dz-image"><img data-dz-thumbnail=""></div>
						<div class="dz-error-mark custom_error_css delete_files" data-imgattr="<?=$buisness_file;?>" data-bank_doc_id="<?=$buisness_doc_id;?>" data-directory-type="business">
						    <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
							<title>Error</title> 
							<defs></defs>
							<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
							    <g id="Check-+-Oval-2" sketch:type="MSLayerGroup" stroke="#747474" stroke-opacity="0.198794158" fill="#FFFFFF" fill-opacity="0.816519475"> 
								<path d="M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" id="Oval-2" sketch:type="MSShapeGroup">
								</path>        
							    </g>      
							</g>   .
						    </svg>  
						</div>
						<div class="dz-details">
						    <div class="dz-size">
							<span data-dz-size=""><strong><?php echo $file_size; ?></strong> KB</span>
						    </div>    
						    <div class="dz-filename"><span data-dz-name=""><?php echo $ext['basename']; ?></span></div>  
						</div>
						<div class="download-mark">
						    <div class="icon"><a href="<?=$buisness_file;?>" download><span class="mdi mdi-download"></span></a></div>
						</div>
					    </div>
					    </div>
				<?php }}}?>
			    
                        </div>
                    </div>
                    <div id="directors" class="tab-pane">
                        <div id="accordionChannelS" class="panel-group accordion">
			    <?php if(!empty($document_director)){ $count_director=count($document_director)-1; $j=1; ?>
			    <input type="hidden" id="countDirector" value="<?=$count_director;?>" > 
			    
			    <?php foreach($document_director as $_document_director){ 
//				echo'<pre>';
//				print_r($_document_director);
//				echo'<pre>';
				$document_file_id=$_document_director->document_file_id;
				$director_file_data = DocumentFile::findOne(['id'=>$document_file_id,'type'=>DocumentFile::DOCUMENT_TYPE_DIRECTORS]);
			    ?>
				<div class="panel panel-default">
				    <div class="panel-heading">
					<h4 class="panel-title">
					    <a data-toggle="collapse" class="collapsed" data-parent="#accordionChannelS" href="#<?='dir_'.$_document_director->id;?>"><i class="icon mdi mdi-chevron-down"></i>Director <?=$j;?></a></h4>
				    </div>

				    <div id="<?='dir_'.$_document_director->id;?>" class="panel-collapse collapse" >
					<div class="panel-body">
					    <div class="table-responsive ">
						<label> Director </label>
						<input type="hidden" class="hidden_directors_file" id="hidden_directors_file_id_0" data-index="0">
						<input type="hidden" class="hidden_user_id" id="" value="<?=$_document_director->id;?>">
						<table id="cats_tbl" style="clear: both" class="table table-striped table-borderless">
						    <tbody>
							<tr>
							    <td width="35%"> First name</td>
							    <td width="65%"><a id="" class="documents_fields first_name" href="#" data-type="text" data-title="Please Enter value"><?=$_document_director->first_name;?></a></td>
							</tr>
							<tr>
							    <td width="35%"> Last  name</td>
							    <td width="65%"><a  id="last_name"class="documents_fields last_name" href="#" data-type="text" data-title="Please Enter value"><?=$_document_director->last_name;?></a></td>
							</tr>
							<tr>
							    <td width="35%"> Date of Birth</td>
							    <td width="65%"><a class="documents_fields_dob dob" href="#"  data-pk="1" data-template="D / MMM / YYYY" data-viewformat="DD/MM/YYYY" data-format="YYYY-MM-DD"data-type="combodate" data-title="Please Enter value"><?=$_document_director->dob;?></a></td>
							</tr>
							<tr>
							    <td width="35%"> Address</td>
							    <td width="65%"><a id="" class="documents_fields address" href="#" data-type="text" data-title="Please Enter value"><?=$_document_director->address;?></a></td>
							</tr>
							<tr>
							    <td width="35%"> Last 4 of Social</td>
							    <td width="65%"> 
								<input type="text" data-mask="last_4_social"  id="last_4_social" value="<?=$_document_director->last_4_social;?>" placeholder="9999" class="form-control last_4_social"></td>
							</tr>
						    </tbody>
						</table>
						
						<?php if(empty($director_file_data)){?>
						<div class="main-content container-fluid">
						    <form  action="" class="dropzone directorsform">
							<div class="dz-message">
							    <div class="icon"><span class="mdi mdi-cloud-upload"></span></div>
							    <h2>Drag and Drop files here</h2>
    <!--                                                        <span class="note">(This is just a demo dropzone. Selected files are <strong>not</strong> actually uploaded.)</span>-->
							</div>
						    </form>
						</div>
						<?php }else{
						    
							$director_file = $director_file_data->file_path;
							$director_doc_id = $director_file_data->id;
							$ext = pathinfo($director_file);
							$file_ext = $ext['extension'];
							echo "<pre>"; print_r($ext); die('End here');
							$file_size = number_format(filesize($director_file)/1024, 1);
							$ext_array = array('png', 'img', 'gif', 'svg', 'jpeg', 'jpg');
							
							if(in_array($file_ext, $ext_array)){ ?>
							    <div class="dropzone dotted_border">
								<div class="dz-preview dz-error dz-complete dz-image-preview">
								    <div class="dz-image"><img width="100%" alt="<?php echo $ext['basename']; ?>" src="<?php echo $director_file; ?>"></div>
								    <div class="dz-error-mark custom_error_css delete_files" data-imgattr="<?=$director_file;?>" data-bank_doc_id="<?=$director_doc_id;?>" data-directory-type="directors">
									<svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
									    <title>Error</title> 
									    <defs></defs>
									    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
										<g id="Check-+-Oval-2" sketch:type="MSLayerGroup" stroke="#747474" stroke-opacity="0.198794158" fill="#FFFFFF" fill-opacity="0.816519475"> 
										    <path d="M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" id="Oval-2" sketch:type="MSShapeGroup">
										    </path>        
										</g>      
									    </g>   .
									</svg>  
								    </div>
								    <div class="dz-details">
									<div class="dz-size">
									    <span data-dz-size=""><strong><?php echo $file_size; ?></strong> KB</span>
									</div>    
									<div class="dz-filename"><span data-dz-name=""><?php echo $ext['basename']; ?></span></div>  
								    </div>
								    <div class="download-mark">
									<div class="icon"><a href="<?=$director_file;?>" download><span class="mdi mdi-download"></span></a></div>
								    </div>
								</div>
							    </div>
							<?php } else{ ?>
							    <div class="dropzone dotted_border">
								<div class="dz-preview dz-file-preview dz-processing dz-error dz-complete dz-image-preview">
								    <div class="dz-image"><img data-dz-thumbnail=""></div>
								    <div class="dz-error-mark custom_error_css delete_files" data-imgattr="<?=$director_file;?>" data-bank_doc_id="<?=$director_doc_id;?>" data-directory-type="directors">
									<svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
									    <title>Error</title> 
									    <defs></defs>
									    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
										<g id="Check-+-Oval-2" sketch:type="MSLayerGroup" stroke="#747474" stroke-opacity="0.198794158" fill="#FFFFFF" fill-opacity="0.816519475"> 
										    <path d="M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" id="Oval-2" sketch:type="MSShapeGroup">
										    </path>        
										</g>      
									    </g>   .
									</svg>  
								    </div>
								    <div class="dz-details">
									<div class="dz-size">
									    <span data-dz-size=""><strong><?php echo $file_size; ?></strong> KB</span>
									</div>    
									<div class="dz-filename"><span data-dz-name=""><?php echo $ext['basename']; ?></span></div>  
								    </div>
								    <div class="download-mark">
									    <div class="icon"><a href="<?=$director_file;?>" download><span class="mdi mdi-download"></span></a></div>
								    </div>
								</div>
							    </div>
						    <?php } }?>
						</div>
					    </div>
					</div>
				    </div>
			<?php $j++; } } else{ ?>
			    <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" class="collapsed" data-parent="#accordionChannelS" href="#director_0"><i class="icon mdi mdi-chevron-down"></i>Director 1</a></h4>
                                </div>
                                <div id="director_0" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <div class="table-responsive ">
                                            <label> Director </label>
                                            <input type="hidden" class="hidden_directors_file" id="hidden_directors_file_id_0" data-index="0">
					    <input type="hidden" class="hidden_user_id" id="" value="0">
                                            <table id="cats_tbl" style="clear: both" class="table table-striped table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <td width="35%"> First name</td>
                                                        <td width="65%"><a id="" class="documents_fields first_name" href="#" data-type="text" data-title="Please Enter value"></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%"> Last  name</td>
                                                        <td width="65%"><a  id="last_name"class="documents_fields last_name" href="#" data-type="text" data-title="Please Enter value"></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%"> Date of Birth</td>
                                                        <td width="65%"><a class="documents_fields_dob dob" href="#"  data-pk="1" data-template="D / MMM / YYYY" data-viewformat="DD/MM/YYYY" data-format="YYYY-MM-DD"data-type="combodate" data-title="Please Enter value"></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%"> Address</td>
                                                        <td width="65%"><a id="" class="documents_fields address" href="#" data-type="text" data-title="Please Enter value"></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%"> Last 4 of Social</td>
                                                        <td width="65%"> 
                                                            <input type="text" data-mask="last_4_social"  id="last_4_social" placeholder="9999" class="form-control last_4_social"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <div class="main-content container-fluid">
                                                <form  action="" class="dropzone directorsform">
                                                    <div class="dz-message">
                                                        <div class="icon"><span class="mdi mdi-cloud-upload"></span></div>
                                                        <h2>Drag and Drop files here</h2>
<!--                                                        <span class="note">(This is just a demo dropzone. Selected files are <strong>not</strong> actually uploaded.)</span>-->
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
			    <?php } ?>
			    
                            
                        </div>

                        <button class="btn btn-space btn-primary <?=$addMoreClass;?> pull-right" ><?=$addMoreText;?></button>
                    </div>
                    <div id="payments" class="tab-pane">

                        <div id="accordionChannelPayment" class="panel-group accordion">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" class="collapsed" data-parent="#accordionChannelPayment" href="#alipay"><i class="icon mdi mdi-chevron-down"></i>Ali Pay</a></h4>
                                </div>
                                <div id="alipay" class="panel-collapse collapse">
                                    <div class="panel-body">

                                        <div class="table-responsive">
                                            <table id="" style="clear: both" class="table table-striped table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <td width="35%">Account #</td>
                                                        <td width="65%"><a class="documents_fields" id="alipay_payment_account_no" href="#" data-type="text" data-title="Please Enter value"><?=$model['alipay_payment_account_no'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Account ID</td>
                                                        <td width="65%"><a class="documents_fields"  id="alipay_payment_account_id" href="#" data-type="text" data-title="Please Enter value" ><?=$model['alipay_payment_account_id'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Account Email</td>
                                                        <td width="65%"><a class="documents_fields" id="alipay_payment_account_email" href="#" data-type="email" data-title="Please Enter value" ><?=$model['alipay_payment_account_email'];?></a></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <div class="panel-heading profile-panel-heading"> 
                                                <div class="title">Account Address</div>
                                            </div>
                                            <table id="general_tbl" style="clear: both" class="table table-striped table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <td width="35%">Address 1</td>
                                                        <td width="65%"><a class="documents_fields" id="alipay_payment_address_1" href="#" data-type="text" data-title="Please Enter value"><?=$model['alipay_payment_address_1'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Address 2</td>
                                                        <td width="65%"><a class="documents_fields"  id="alipay_payment_address_2" href="#" data-type="text" data-title="Please Enter value" ><?=$model['alipay_payment_address_1'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">City</td>
                                                        <td width="65%"><a id="alipay_payment_account_city" data-title="Start typing City.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:"  class="payment_account_city"><?=$model['alipay_payment_account_city'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">State</td>
                                                        <td width="65%"><a  id="alipay_payment_account_state" data-title="Start typing State.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:"  data-type="email" class="payment_account_state" ><?=$model['alipay_payment_account_state'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Country</td>
                                                        <td width="65%"><a  id="alipay_payment_account_country" data-title="Start typing Country.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:"  class="payment_account_country"><?=$model['alipay_payment_account_country'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Zip Code</td>
                                                        <td width="65%"><a class="documents_fields" id="alipay_payment_account_zip_code" href="#" data-type="text" data-title="Please Enter value" ><?=$model['alipay_payment_account_zip_code'];?></a></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" class="collapsed" data-parent="#accordionChannelPayment" href="#dinpay"><i class="icon mdi mdi-chevron-down"></i>DinPay</a></h4>
                                </div>
                                <div id="dinpay" class="panel-collapse collapse">
                                    <div class="panel-body">

                                        <div class="table-responsive">
                                            <table id="" style="clear: both" class="table table-striped table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <td width="35%">Account #</td>
                                                        <td width="65%"><a class="documents_fields" id="dinpay_payment_account_no" href="#" data-type="text" data-title="Please Enter value"><?=$model['dinpay_payment_account_no'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Account ID</td>
                                                        <td width="65%"><a class="documents_fields"  id="dinpay_payment_account_id" href="#" data-type="text" data-title="Please Enter value" ><?=$model['dinpay_payment_account_id'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Account Email</td>
                                                        <td width="65%"><a class="documents_fields" id="dinpay_payment_account_email" href="#" data-type="email" data-title="Please Enter value" ><?=$model['dinpay_payment_account_email'];?></a></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <div class="panel-heading profile-panel-heading"> 
                                                <div class="title">Account Address</div>
                                            </div>
                                            <table id="" style="clear: both" class="table table-striped table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <td width="35%">Address 1</td>
                                                        <td width="65%"><a class="documents_fields" id="dinpay_payment_address_1" href="#" data-type="text" data-title="Please Enter value"><?=$model['dinpay_payment_address_1'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Address 2</td>
                                                        <td width="65%"><a class="documents_fields"  id="dinpay_payment_address_2" href="#" data-type="text" data-title="Please Enter value" ><?=$model['dinpay_payment_address_2'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">City</td>
                                                        <td width="65%"><a id="dinpay_payment_account_city" data-title="Start typing City.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:"  class="payment_account_city"><?=$model['dinpay_payment_account_city'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">State</td>
                                                        <td width="65%"><a  id="dinpay_payment_account_state" data-title="Start typing State.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:"  data-type="email" class="payment_account_state" ><?=$model['dinpay_payment_account_state'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Country</td>
                                                        <td width="65%"><a  id="dinpay_payment_account_country" data-title="Start typing Country.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:"  class="payment_account_country"><?=$model['dinpay_payment_account_country'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Zip Code</td>
                                                        <td width="65%"><a class="documents_fields" id="dinpay_payment_account_zip_code" href="#" data-type="text" data-title="Please Enter value" ><?=$model['dinpay_payment_account_zip_code'];?></a></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" class="collapsed" data-parent="#accordionChannelPayment" href="#payoneer"><i class="icon mdi mdi-chevron-down"></i>Payoneer</a></h4>
                                </div>
                                <div id="payoneer" class="panel-collapse collapse">
                                    <div class="panel-body">

                                        <div class="table-responsive">
                                            <table id="" style="clear: both" class="table table-striped table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <td width="35%">Account #</td>
                                                        <td width="65%"><a class="documents_fields" id="payoneer_payment_account_no" href="#" data-type="text" data-title="Please Enter value"><?=$model['payoneer_payment_account_no'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Account ID</td>
                                                        <td width="65%"><a class="documents_fields"  id="payoneer_payment_account_id" href="#" data-type="text" data-title="Please Enter value" ><?=$model['payoneer_payment_account_id'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Account Email</td>
                                                        <td width="65%"><a class="documents_fields" id="payoneer_payment_account_email" href="#" data-type="email" data-title="Please Enter value" ><?=$model['payoneer_payment_account_email'];?></a></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <div class="panel-heading profile-panel-heading"> 
                                                <div class="title">Account Address</div>
                                            </div>
                                            <table id="general_tbl" style="clear: both" class="table table-striped table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <td width="35%">Address 1</td>
                                                        <td width="65%"><a class="documents_fields" id="payoneer_payment_address_1" href="#" data-type="text" data-title="Please Enter value"><?=$model['payoneer_payment_address_1'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Address 2</td>
                                                        <td width="65%"><a class="documents_fields"  id="payoneer_payment_address_2" href="#" data-type="text" data-title="Please Enter value" ><?=$model['payoneer_payment_address_2'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">City</td>
                                                        <td width="65%"><a id="payoneer_payment_account_city" data-title="Start typing City.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:"  class="payment_account_city"><?=$model['payoneer_payment_account_city'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">State</td>
                                                        <td width="65%"><a  id="payoneer_payment_account_state" data-title="Start typing State.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:"  data-type="email" class="payment_account_state" ><?=$model['payoneer_payment_account_state'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Country</td>
                                                        <td width="65%"><a  id="payoneer_payment_account_country" data-title="Start typing Country.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:"  class="payment_account_country"><?=$model['payoneer_payment_account_country'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Zip Code</td>
                                                        <td width="65%"><a class="documents_fields" id="payoneer_payment_account_zip_code" href="#" data-type="text" data-title="Please Enter value" ><?=$model['payoneer_payment_account_zip_code'];?></a></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" class="collapsed" data-parent="#accordionChannelPayment" href="#worldfirst"><i class="icon mdi mdi-chevron-down"></i>WorldFirst</a></h4>
                                </div>
                                <div id="worldfirst" class="panel-collapse collapse">
                                    <div class="panel-body">

                                        <div class="table-responsive">
                                            <table id="" style="clear: both" class="table table-striped table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <td width="35%">Account #</td>
                                                        <td width="65%"><a class="documents_fields" id="worldfirst_payment_account_no" href="#" data-type="text" data-title="Please Enter value"><?=$model['worldfirst_payment_account_no'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Account ID</td>
                                                        <td width="65%"><a class="documents_fields"  id="worldfirst_payment_account_id" href="#" data-type="text" data-title="Please Enter value" ><?=$model['worldfirst_payment_account_id'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Account Email</td>
                                                        <td width="65%"><a class="documents_fields" id="worldfirst_payment_account_email" href="#" data-type="email" data-title="Please Enter value" ><?=$model['worldfirst_payment_account_email'];?></a></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <div class="panel-heading profile-panel-heading"> 
                                                <div class="title">Account Address</div>
                                            </div>
                                            <table id="general_tbl" style="clear: both" class="table table-striped table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <td width="35%">Address 1</td>
                                                        <td width="65%"><a class="documents_fields" id="worldfirst_payment_address_1" href="#" data-type="text" data-title="Please Enter value"><?=$model['worldfirst_payment_address_1'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Address 2</td>
                                                        <td width="65%"><a class="documents_fields"  id="worldfirst_payment_address_2" href="#" data-type="text" data-title="Please Enter value" ><?=$model['worldfirst_payment_address_2'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">City</td>
                                                        <td width="65%"><a id="worldfirst_payment_account_city" data-title="Start typing City.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:"  class="payment_account_city"><?=$model['worldfirst_payment_account_city'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">State</td>
                                                        <td width="65%"><a  id="worldfirst_payment_account_state" data-title="Start typing State.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:"  data-type="email" class="payment_account_state" ><?=$model['worldfirst_payment_account_state'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Country</td>
                                                        <td width="65%"><a  id="worldfirst_payment_account_country" data-title="Start typing Country.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:"  class="payment_account_country"><?=$model['worldfirst_payment_account_country'];?></a></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%">Zip Code</td>
                                                        <td width="65%"><a class="documents_fields" id="worldfirst_payment_account_zip_code" href="#" data-type="text" data-title="Please Enter value" ><?=$model['worldfirst_payment_account_zip_code'];?></a></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab_up_btn_div custOMPerf">

                            <button class="btn btn-space btn-primary" onclick="addUpdateDocuments();">Update</button>
                    </div>
	    </div>
	</div>
    </div>
</div> 
    
<div id="documentssaved" tabindex="-1" role="dialog" style="display: none;" class="modal fade">
    <div class="modal-dialog">
      <div class="modal-content">
	    <div class="modal-header">
	      <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close"></span></button>
	    </div>
	    <div class="modal-body">
	      <div class="text-center">
		    <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
		    <h3>Success!</h3>
		    <p>Data has been saved successfully.</p>
		    <div class="xs-mt-50">
		      <button type="button" data-dismiss="modal" class="btn btn-space btn-default close_corporate">Close</button>
		    </div>
	      </div>
	    </div>
	    <div class="modal-footer"></div>
      </div>
    </div>
</div>
    
    
<div id="corporate-delete-modal-warning" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
	     <input type="hidden" id="modal_corporate_image_path" value="">
	     <input type="hidden" id="modal_corporate_bank_id" value="">
	     <input type="hidden" id="modal_corporate_type" value="">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close"></span></button>
            </div>
	 
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-warning"><span class="modal-main-icon mdi mdi-alert-triangle"></span></div>
                    <h3>Warning!</h3>
                    <p>Are you sure you want to delete?</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default">Cancel</button>
                        <button type="button" id="corporate_proceed_button" class="btn btn-space btn-warning">Proceed</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
