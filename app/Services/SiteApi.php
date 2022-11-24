<?php

namespace App\Services;
use Illuminate\Http\Request;
use DB;
use App\Models\Service;
use App\Models\SiteMenu;
use App\Models\FooterSection;
use App\Models\Site;
use App\Models\LocationDetail;
use App\Models\SiteContent;
use App\Models\Template;
use App\Models\TextBlock;
use App\Models\CustomTag;
use App\Services\SeoApi;
class SiteApi
{
    public function __construct () {
        $this->type = 'home';
		$this->custom_keyword = '';
        $this->location = '';
		$this->state = '';
		$this->stateCodeValue = '';
		$this->county = '';
        $this->city = '';
        $this->zip = '';
        $this->domain = '';
		$this->phone = '';
		$this->latitude = '';
		$this->longitude = '';
		$this->allServicesList = null;
        $this->separatePage = null;
        $this->ServicePage = null;
        $this->text_block_1= '';
        $this->text_block_2= '';
        $this->text_block_3= '';
        $this->text_block_4= '';
	}
    public function generateApiResponse($requestAll) { 
        $keyword_location_url = '';
        $site = Site::on('onthefly')
                ->select('*')
                ->first();
				
		$this->state = $site->state;
		$this->stateCodeValue = $site->state_code;
		$this->county = $site->county;
		$this->city = $site->city;
		$this->phone = $site->phone;
		$this->latitude = $site->latitude;
		$this->longitude = $site->longitude;
		$this->domain =    $site->domain;
		$this->custom_keyword = $site->service_id;
		$this->location = $site->city;

		if(!empty($requestAll['type'])) {
			$allTemplatesRow = Template::on('onthefly')->select('html')->where('type',$requestAll['type'])->first();
			if(!empty($allTemplatesRow)) {
				$this->type = $requestAll['type'];
			} else {
				return response()->json(['response' =>'Please check your parameter'],400);
			}
			if($this->type == 'service' || $this->type == 'zip') {
				if(!empty($requestAll['service'])) {
					if(!empty($this->getKeywordId(ucwords(implode(' ',explode('-',$requestAll['service'])))))) {
						$this->custom_keyword = ucwords(implode(' ',explode('-',$requestAll['service'])));
					} else {
						return response()->json(['response' =>'Please check your parameter'],400);
					}
				}
			}
			if($this->type == 'zip') {
				if(!empty($requestAll['zip'])) {
					$checkZip = LocationDetail::on('onthefly')
								->select('zip')
								->where('zip',$requestAll['zip'])
								->first();
					if(!empty($checkZip)) {
						$this->location = $requestAll['zip'];
						$this->zip = $requestAll['zip'];
						if(!empty($requestAll['service'])) {
							$this->ServicePage = 1;
						}
					} else {
						return response()->json(['response' =>'Please check your parameter'],400);
					}
				} else {
					return response()->json(['response' =>'Please check your parameter'],400);
				}
			}
		}  else {
			return response()->json(['response' =>'Please check your parameter'],400);
		}
       
       
        $allServicesList = $this->getServicesList();
        $zipcodes = $this->getZipcodesList();
        $textBlockAndMetaTagInfomation = $this->getTextBlockAndMetaTagInfomation();
		$this->getLatLongPhone();
		if(!empty($this->phone)) {
			$this->phone ='('.substr($this->phone,0,3).")-".substr($this->phone,3,3)."-".substr($this->phone,6);
		}
		/** 
		 *  Here We are stating schemas for api
		*/
		$seo = new SeoApi($this->type,$this->state,$this->stateCodeValue,$this->city,$this->zip,$this->phone,$this->domain,$this->custom_keyword);
		$seoSchemaResponse = $seo->generateSeoSchemaResponse();
        // if(!empty($textBlockAndMetaTagInfomation['text_block_1'])) {
		// 	$this->text_block_1 = $textBlockAndMetaTagInfomation['text_block_1'];
		// }
	
		// if(!empty($textBlockAndMetaTagInfomation['text_block_2'])) {
		// 	$this->text_block_2 = $textBlockAndMetaTagInfomation['text_block_2'];
		// }
	
		// if(!empty($textBlockAndMetaTagInfomation['text_block_3'])) {
		// 	$this->text_block_3 = $textBlockAndMetaTagInfomation['text_block_3'];
		// }
	
		// if(!empty($textBlockAndMetaTagInfomation['text_block_4'])) {
		// 	$this->text_block_4 = $textBlockAndMetaTagInfomation['text_block_4'];
		// }
		
		if(!empty($textBlockAndMetaTagInfomation['meta_heading'])) {
			$meta_heading = $textBlockAndMetaTagInfomation['meta_heading'];
		}
		if(!empty($textBlockAndMetaTagInfomation['meta_title'])) {
			$meta_title = $textBlockAndMetaTagInfomation['meta_title'];
		}
		if(!empty($textBlockAndMetaTagInfomation['meta_description'])) {
			$meta_description = $textBlockAndMetaTagInfomation['meta_description'];
		}
        $searchKey=[
			"[meta_heading_h1]","[meta_title]","[meta_description]",
			"[keyword]","[city-state-zipcode]","[phone]","[keyword_location_url]","[latitude]","[longitude]","[text_block_1]","[text_block_2]","[text_block_3]","[text_block_4]","[state]","[county]","[city]",'"[zip_codes]"','"[related_services]"',"[Breadcrumbs]",
			"[StateCode]",'"[schemas]"'
		];
        if($this->type == 'zip' && !empty($this->servicePage)) {
			$keyword_location_url=$this->domain.'/'.$this->makeUrl($this->zip)."/".$this->makeUrl($this->custom_keyword);
			$keywordAnchorTag = '<a style="color:white;text-decoration:underline" href='.$this->domain.'/'.$this->makeUrl($this->custom_keyword).'> our '.$this->custom_keyword.' Company</a>';
		}  else if($this->type == 'zip') {
			$keyword_location_url=$this->domain.'/'.$this->makeUrl($this->zip);
			$keywordAnchorTag = '<a style="color:white;text-decoration:underline" href='.$this->domain.'> our '.$this->custom_keyword.' company</a>';
		} else if($this->type == 'service') {
			$keyword_location_url=$this->domain.'/'.$this->makeUrl($this->custom_keyword);
		}  

		// echo "<pre>";print_r(json_encode($zipcodes));die;
		$replaceKey=[ucwords($meta_heading),$meta_title,$meta_description,
			ucwords($this->custom_keyword),ucwords($this->location),$this->phone,$keyword_location_url,$this->latitude,$this->longitude,$this->text_block_1,$this->text_block_2,$this->text_block_3,$this->text_block_4,ucwords($this->state),ucwords($this->county),ucwords($this->city),json_encode($zipcodes),json_encode($allServicesList),$keyword_location_url,$this->stateCodeValue,$seoSchemaResponse
		];		

		return str_replace($searchKey,$replaceKey,$allTemplatesRow->html);
    }


    public function getServicesList() {
		$ralated_service = $this->getServicesArray();
		$keyWordId = $this->getKeywordId($this->custom_keyword);
        $services = [];
		$serviceData = [];
		foreach ($ralated_service as $key =>  $service)	{
			if($key == $keyWordId || strtolower($service) == 'towing') {
				continue;
			}
            if($this->type =='zip') {
                $url = $this->makeUrl($this->zip)."/".$this->makeUrl($service);
            } else {
                $url = '/'.$this->makeUrl($service);  
            }
			$serviceData['name'] = $service;
			$serviceData['path'] = $url;
            array_push($services,$serviceData);
		}
		return $services;
	}

    function getServicesArray() {
		$result =  Service::on('onthefly')
                    ->select('id','service_name')
                    ->get()
                    ->toArray();
		$services = [];
		foreach ($result as $row) {
	 		$services[$row['id']] = $row['service_name'];			
		}		
		return $services;
	}

    function getKeywordId($site_link_keyword) {
		$result = Service::on('onthefly')
                    ->select('id')
                    ->where('service_name',$site_link_keyword)
                    ->first();
        if(!empty($result->id)) {
		    return $result->id;
        }   else {
            return null;
        }    
	}

    function makeUrl($initial_string) {
		$final_string = str_replace(
			array(', ',' ','/',"&","  "),
			array('-','-','',""," "),
			$initial_string
		);
		return strtolower($final_string);
	}

    function getZipcodesList() {
		$allZipCode  = $this->getDistinctZipcodeByCity();
		$zipcodeData=[];
		$zipcodes=[];

		foreach ($allZipCode as $zip){
			if(!empty($this->ServicePage)) {
				$url= $this->makeUrl($zip)."/".$this->makeUrl($this->custom_keyword);
			} else {
				$url='/'.$this->makeUrl($zip);
			}
			$zipcodeData['name'] = $zip;
			$zipcodeData['path'] = $url;
            array_push($zipcodes,$zipcodeData);
		}
		return $zipcodes;
	}

    function getDistinctZipcodeByCity() {
		$result = LocationDetail::on('onthefly')
                    ->select('zip')
                    ->distinct()
                    ->where('state_name',$this->state)
                    ->where('city',$this->city)
                    ->get()
                    ->toArray();
		$allZipcodeList = [];
		foreach ($result as $row) {
			$allZipcodeList[] = $row['zip'];
		}
		sort($allZipcodeList);
		return $allZipcodeList;
	}
    function getTextBlockAndMetaTagInfomation() {
		$textBlockArray = [];
		$textBlockIdArray = [];
		$metaInfomationArray = [];
		$textBlockMetaInfomation = [];
		$keyWordId =null;
	    $keyWordId = $this->getKeywordId($this->custom_keyword);
        $mainServiceData = SiteContent::on('onthefly')
                    ->select('*')
                    ->distinct()
                    ->where('location',$this->location)
                    ->where('service_id',$keyWordId)
                    ->where('type',$this->type)
                    ->first();
		if(!empty($mainServiceData->meta_heading)) {
			$searchKey=[
				"[keyword]","[phone]","[city-state-zipcode]","[city]"
			];
			$replaceKey=[
				ucwords($this->custom_keyword),$this->phone,ucwords($this->location),$this->city
			];
			
			if(!empty($mainServiceData->text_block_1) || !empty($mainServiceData->text_block_2) || !empty($mainServiceData->text_block_3) || !empty($mainServiceData->text_block_4)) {
				if(!empty($mainServiceData->text_block_1)) {
					$textBlockIdArray['text_block_1'] =nl2br($mainServiceData->text_block_1);
				}
				if(!empty($mainServiceData->text_block_2)) {
					$textBlockIdArray['text_block_2'] ='<p>' . implode('</p><p>', array_filter(explode('\n',$mainServiceData->text_block_2))) . '</p>';
				}
				if(!empty($mainServiceData->text_block_3)) {
					$textBlockIdArray['text_block_3']  ='<p>' . implode('</p><p>', array_filter(explode('\n',$mainServiceData->text_block_3))) . '</p>';
	
				} if(!empty($mainServiceData->text_block_4)) {
					$textBlockIdArray['text_block_4'] ='<p>' . implode('</p><p>', array_filter(explode('\n',$mainServiceData->text_block_4))) . '</p>';
				}
				foreach($textBlockIdArray as $textBlock => $value) {
					$mainContent = str_replace($searchKey,$replaceKey,$value);
					$textBlockMetaInfomation[$textBlock] ='<p>' . implode('</p><p>', array_filter(explode('\n',$mainContent))) . '</p>';
				}
			}
			
			if(!empty($mainServiceData->meta_heading) || !empty($mainServiceData->meta_title) || !empty($mainServiceData->meta_description)) {
				if(!empty($mainServiceData->meta_heading)) {
					$metaInfomationArray['meta_heading'] = $mainServiceData->meta_heading;
				}
				if(!empty($mainServiceData->meta_title)) {
					$metaInfomationArray['meta_title'] = $mainServiceData->meta_title;
				}
				if(!empty($mainServiceData->meta_description)) {
					$metaInfomationArray['meta_description'] = $mainServiceData->meta_description;
	
				}
				foreach($metaInfomationArray as $metaKey => $metaKeyData) {
					$mainContent = str_replace($searchKey,$replaceKey,$metaKeyData);
					$textBlockMetaInfomation[$metaKey] =$mainContent;
				}
			}
		} else {
			$textBlockMetaInfomation = $this->insertContentIfNotExist();
		}
		if(!empty($textBlockMetaInfomation)) {
				return $textBlockMetaInfomation;
		} else {
			return null;
		}
	}

	function insertContentIfNotExist() {
		$textBlockMetaInfomation = [];
		$customeTag = $this->getCustomTag();
		$allMetaTag = $this->getMetaTag();
		$generateBlockArray = $this->getTextBlock();
		$customTagArrayWithRandomString = [];
		$allMetaTagsArray = [];
		$searchCustomKey = [];
		$text_block_1 = null;
		$text_block_2 = null;
		$text_block_3 = null;
		$text_block_4 = null;
		$searchMetaCustomKey = null;
		$replaceMetaCoustomValue = null;
		$meta_title = null;
		$meta_heading_h1 = null;
		$meta_description = null;
		$replaceCoustomValue = [];
		if(!empty($customeTag)) {
			foreach($customeTag as $key => $value) {
				$generateArray = explode('||',$value);
				if(!empty($generateArray)) {
					shuffle($generateArray);    
					$customTagArrayWithRandomString[$key] = $generateArray[0];
				} else {
					$customTagArrayWithRandomString[$key] = $value;
				}
			}
		}
		if(!empty($customTagArrayWithRandomString)) {
			$searchCustomKey = array_keys($customTagArrayWithRandomString);
			$replaceCoustomValue = array_values($customTagArrayWithRandomString);
		}
		foreach ($allMetaTag as $key=> $value) {

			$generateMetaArray = [];
			$generateMetaArray = explode('||',$value);
			if(!empty($generateMetaArray)) {
				shuffle($generateMetaArray);

				$allMetaTagsArray[$key] = str_replace($searchCustomKey,$replaceCoustomValue,$generateMetaArray[0]);	
			} else {

				$allMetaTagsArray[$key] =str_replace($searchCustomKey,$replaceCoustomValue,$value);		
			}
		}
		if(!empty($allMetaTagsArray)) {
			$searchMetaCustomKey = array_keys($allMetaTagsArray);
			$replaceMetaCoustomValue = array_values($allMetaTagsArray);
		}

		$meta_title = !empty($replaceMetaCoustomValue[0]) ? $replaceMetaCoustomValue[0]:0;
		$meta_heading_h1 = !empty($replaceMetaCoustomValue[1]) ? $replaceMetaCoustomValue[1]:0;
		$meta_description = !empty($replaceMetaCoustomValue[2]) ? $replaceMetaCoustomValue[2]:0;
        if(!empty($generateBlockArray)) {
            shuffle($generateBlockArray);		
            if(!empty($generateBlockArray[0]['block'])) {
                $text_block_1 = str_replace($searchCustomKey,$replaceCoustomValue,$generateBlockArray[0]['block']);
            } if(!empty($generateBlockArray[1]['block'])) {
                $text_block_2 = str_replace($searchCustomKey,$replaceCoustomValue,$generateBlockArray[1]['block']);
            } if(!empty($generateBlockArray[2]['block'])) {
                $text_block_3 = str_replace($searchCustomKey,$replaceCoustomValue,$generateBlockArray[2]['block']);
            } if(!empty($generateBlockArray[3]['block'])) {
                $text_block_4 = str_replace($searchCustomKey,$replaceCoustomValue,$generateBlockArray[3]['block']);
            }
        }
        SiteContent::on('onthefly')->create([
            'service_id' => $this->getKeywordId($this->custom_keyword),
            'type' => $this->type,
            'text_block_1' => $text_block_1,
            'text_block_2' => $text_block_2,
            'text_block_3' => $text_block_3,
            'text_block_4' => $text_block_4,
            'meta_heading' => $meta_heading_h1,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'location' => $this->location
        ]);
        
		$searchKey=[
			"[keyword]","[phone]","[city-state-zipcode]","[city]"
		];
		$replaceKey=[
			ucwords($this->custom_keyword),$this->phone,ucwords($this->location),$this->city
		];
		
		if(!empty($text_block_1) || !empty($text_block_2) || !empty($text_block_3) || !empty($text_block_4)) {
			if(!empty($text_block_1)) {
				$textBlockMetaInfomation['text_block_1'] =str_replace($searchKey,$replaceKey,'<p>' . implode('</p><p>', array_filter(explode('\n',$text_block_1))) . '</p>');
			}
			if(!empty($text_block_2)) {
				$textBlockMetaInfomation['text_block_2'] =str_replace($searchKey,$replaceKey,'<p>' . implode('</p><p>', array_filter(explode('\n',$text_block_2))) . '</p>');
			}
			if(!empty($text_block_3)) {
				$textBlockMetaInfomation['text_block_3']  =str_replace($searchKey,$replaceKey,'<p>' . implode('</p><p>', array_filter(explode('\n',$text_block_3))) . '</p>');

			} if(!empty($text_block_4)) {
				$textBlockMetaInfomation['text_block_4'] =str_replace($searchKey,$replaceKey,'<p>' . implode('</p><p>', array_filter(explode('\n',$text_block_4))) . '</p>');
			}
		}
		if(!empty($meta_heading_h1) || !empty($meta_title) || !empty($meta_description)) {
			if(!empty($meta_heading_h1)) {
				$textBlockMetaInfomation['meta_heading'] = str_replace($searchKey,$replaceKey,$meta_heading_h1);
			}
			if(!empty($meta_title)) {
				$textBlockMetaInfomation['meta_title'] = str_replace($searchKey,$replaceKey,$meta_title);
			}
			if(!empty($meta_description)) {
				$textBlockMetaInfomation['meta_description'] = str_replace($searchKey,$replaceKey,$meta_description);
			}
		}
		if(!empty($textBlockMetaInfomation)) {
			return $textBlockMetaInfomation;
		} else {
			return null;
		}
	}

	function getCustomTag() {
		$allTagsArray = [];
		$searchCustomKey = [];
		$replaceCoustomValue = [];
		$metaTagaArray = [];
		$customTagAndMetaTag = [];
		$genresArray = [];
		$customTagArrayWithRandomString = [];
        $mainTagArray = CustomTag::on('onthefly')
                    ->select('tagName','description')
                    ->get()
                    ->toArray();
		if(!empty($mainTagArray)) {
			foreach ($mainTagArray as $key=> $row) {
				$allTagsArray[$row['tagName']] = $row['description'];			
			}
		}
		return $allTagsArray;
	}

	function getMetaTag() {
		$mainMetaArray = Template::on('onthefly')
                    ->select('meta_title','meta_heading','meta_description')
                    ->where('type',$this->type)
                    ->get()->toArray();
        if(!empty($mainMetaArray)) {
            return $mainMetaArray[0];
        } else {

        }
		return $mainMetaArray;
	}

	function getTextBlock() {
		global $connection;
		$textBlockArray = [];
		$textBlockIdArray = [];
		$mainServiceData = TextBlock::on('onthefly')
                    ->select('*')
                    ->get()
                    ->toArray();
		if(!empty($mainServiceData)) {
			return $mainServiceData;
		} else {
			return null;
		}
	}

    function getLatLongPhone() {
		$query = '';
        if($this->type == 'zip') {
			$query = 'SELECT location_detail.zip_latitude as latitude ,location_detail.zip_longitude as longitude,location_detail.areacode,phone.phoneNumber FROM location_detail LEFT JOIN phone ON location_detail.areacode=phone.areacode where location_detail.approved=1 and zip="'.$this->zip.'" limit 1';
		} else if($this->type == 'state') {
			$query = "SELECT location_detail.state_latitude as latitude,location_detail.state_longitude as longitude,location_detail.areacode,phone.phoneNumber FROM location_detail LEFT JOIN phone ON location_detail.areacode=phone.areacode where location_detail.approved=1 and state_name=".'"'.$this->state.'"'." COLLATE NOCASE limit 1";
		} else if($this->type == 'county') {
			$query = "SELECT location_detail.county_latitude as latitude,location_detail.county_longitude as longitude,location_detail.areacode,phone.phoneNumber FROM location_detail LEFT JOIN phone ON location_detail.areacode=phone.areacode where location_detail.approved=1 and state_name=".'"'.$this->state.'"'." COLLATE NOCASE and county=".'"'.$this->county.'"'." COLLATE NOCASE limit 1";
		} else if($this->type == 'city') {			
			$query = "SELECT location_detail.city_latitude as latitude,location_detail.city_longitude as longitude,location_detail.areacode,phone.phoneNumber FROM location_detail LEFT JOIN phone ON location_detail.areacode=phone.areacode where location_detail.approved=1 and state_name=".'"'.$this->state.'"'." COLLATE NOCASE and city=".'"'.$this->city.'"'." COLLATE NOCASE limit 1";
		}
		if(!empty($query)) {
			$city = DB::connection('onthefly')->select($query);
			if(!empty($city) && (!empty($city[0]->latitude) || !empty($city[0]->longitude) || !empty($city[0]->phoneNumber))) {
				if(!empty($city[0]->latitude)) {
					$this->latitude = $city[0]->latitude;
				}
				if(!empty($city[0]->longitude)) {
					$this->longitude = $city[0]->latitude;
				}
				if(!empty($city[0]->phoneNumber)) {
					$this->phone = $city[0]->phoneNumber;
				}
			} 
		}
	}
}
