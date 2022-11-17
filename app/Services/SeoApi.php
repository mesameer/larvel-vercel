<?php

namespace App\Services;
use Illuminate\Http\Request;
use DB;
use App\Models\SeoFaq;
use App\Models\SeoHowTo;
use App\Models\SeoStepsHowToImage;
use App\Models\SeoVideos;
use App\Models\SeoSpeakable;
use App\Models\SeoLocalBusiness;
use App\Models\SeoLinkJuice;
use App\Models\SeoService;
use App\Services\SiteApi;

class SeoApi extends SiteApi {
	public function __construct ($type,$state,$stateCodeValue,$city,$zip,$phone,$domain,$custom_keyword) {
	   	$this->type = $type;
		$this->custom_keyword = $custom_keyword;
		$this->state = $state;
		$this->stateCodeValue = $stateCodeValue;
		$this->county = '';
        $this->city = $city;
        $this->zip = $zip;
        $this->siteUrl = $domain;
		$this->phone = $phone;
		$this->service_id = $this->getKeywordId($this->custom_keyword);
		$this->formate_custom_keyword = $this->makeUrl($this->custom_keyword);
	}
	function generateSeoSchemaResponse() {
		$schemasArray = [];
		$schemasArray['faqSchemas'] = $this->getFAQ();
		$schemasArray['logoSchemas'] = $this->getLogo();
		$schemasArray['howTOSchemas'] = $this->getHOwTO();
		$schemasArray['videoSchemas'] = $this->getVideos();
		$schemasArray['speakableSchemas'] = $this->getSpeakable();
		$schemasArray['localBusinessSchemas'] = $this->getLocalBusiness();
		$schemasArray['serviceSchemas'] = $this->getService();
		return json_encode([$schemasArray]);
	}

	function getFAQ() {
		$schema = [];
		$mainFAQData = SeoFaq::on('onthefly')
						->select('*')
						->where('service_id',$this->service_id)
						->get()
						->toArray();
		if(!empty($mainFAQData)) {
			$schema = [
				'@context'   => "https://schema.org",
				'@type'      => "FAQPage",
				'mainEntity' => array()
			];
			foreach($mainFAQData as $result) {
				$questions = [
					'@type'          => 'Question',
					'name'           => $result['question'],
					'acceptedAnswer' => [
						'@type' => "Answer",
						'text' => $result['answer']
					]
				];
				array_push($schema['mainEntity'], $questions);
			}
			return  $schema;
		} else {
			return null;
		}
	}

	function getLogo() {
		$schema = [
			'@context' => "https://schema.org",
			'@type' => "Organization",
			"name" => "towingminneapolis",
			"url" => $this->siteUrl,
			"logo" =>  $this->siteUrl."/theme/images/logo.png"
		];
		return  $schema;
	}

	function getHOwTO() {
		$schema = [];
		$mainHOWTOData = SeoHowTo::on('onthefly')
						->select('*')
						->where('service_id',$this->service_id)
						->get()
						->toArray();
		if(!empty($mainHOWTOData)) {
			$schema = [
				'@context'   => "https://schema.org",
				'@type'      => "HowTo",
				"name" => $mainHOWTOData[0]['name'],
				"description" => $mainHOWTOData[0]['description'],
				"image" =>  $this->siteUrl.'/seo/howTOImages/'.$mainHOWTOData[0]['image'],
				'step' => array()
			];
			$stepsDataArray = SeoStepsHowToImage::on('onthefly')
								->select('*')
								->where('service_id',$this->service_id)
								->get()
								->toArray();
			if(!empty($stepsDataArray)) {
				foreach($stepsDataArray as $row) {
					$steps = [
						'@type'   => 'HowToStep',
						'name'    => $row['name'],
						"image"	  => $this->siteUrl.'/seo/howToImages/steps'.$row['image'],
						"text" =>  $row['content']
					];
					array_push($schema['step'], $steps);
				}
			}
			return  $schema;
		} else {
			return null;
		}
	}

	function getVideos() {
		$schema = [];
		$mainVideoData = SeoVideos::on('onthefly')
						->select('*')
						->where('service_id',$this->service_id)
						->get()
						->toArray();

		if(!empty($mainVideoData)) {
			$schema = [
				'@context'   => "https://schema.org",
				'@type'      => "VideoObject",
				"name" => $mainVideoData[0]['name'],
				'thumbnailUrl' => 'https://www.youtube.com/embed/'.$mainVideoData[0]['thumbnail_url'],
				'embedUrl' => 'https://www.youtube.com/embed/'.$mainVideoData[0]['embed_url'],
				"description" => $mainVideoData[0]['description'],
				"uploadDate" => $mainVideoData[0]['uploadDate'],
				"potentialAction" => [
					"@type" => "SeekToAction",
					"target" => "https://www.youtube.com/watch?v=".$mainVideoData[0]['video_url']."?t={seek_to_second_number}",
					"startOffset-input" =>  "required name=seek_to_second_number",
				],
			];
			return  $schema;
		} else {
			return null;
		}
	}

	function getSpeakable() {
		$schema = [];
		$mainSpeakableData = SeoSpeakable::on('onthefly')
								->select('*')
								->where('service_id',$this->service_id)
								->where('city',$this->city)
								->get()
								->toArray();
		
		if(!empty($mainSpeakableData)) {
			$cssSelector = explode(',',$mainSpeakableData[0]['css_selector']);
			$schema = [
				'@context'   => "https://schema.org",
				'@type'      => $mainSpeakableData[0]['schema_type'],
				"name" => $mainSpeakableData[0]['name'],
				"speakable" => [
					"@type" => "SpeakableSpecification",
					"cssSelector" =>  [$cssSelector],
				],
				"url" => $this->siteUrl.'/'.$this->custom_keyword,
			];
			return  $schema;
		} else {
			return null;
		}
	}

	function getLocalBusiness() {
		$schema = [];
		$mainLocalBusinessData = SeoLocalBusiness::on('onthefly')
							->select('*')
							->where('service_id',$this->service_id)
							->where('type',$this->type)
							->get()
							->toArray();

		if(!empty($mainLocalBusinessData)) {
			$siteUrlSlug = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$schema = [
				'@context'   => "https://schema.org",
				'@type'      => "LocalBusiness",
				"name" => $mainLocalBusinessData[0]['name'],
				"image" => $this->siteUrl."/theme/images/logo.png",
				"@id" => $this->siteUrl,
				"url" => $siteUrlSlug,
				"telephone" => $this->phone,
				"address" => [
					"@type" => "PostalAddress",
					"streetAddress" => "",
					"addressLocality" => $this->city,
					"addressRegion" => $this->stateCodeValue,
					"postalCode" => "",
					"addressCountry" => "US"
				],
				"openingHoursSpecification" => [
					"@type" => "OpeningHoursSpecification",
					"dayOfWeek" => ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],
					"opens" => "00:00",
					"closes" => "23:59"
				],
			];

			if($this->type == 'zip') {
				$schema['department']  = [
					"@type" =>  "AutomotiveBusiness",
					"name" => ucfirst($this->custom_keyword).' '.ucfirst($this->city),
					"image" => "",
					"telephone" => $this->phone,
					"address" => [
						"@type" => "PostalAddress",
						"streetAddress" => "",
						"addressLocality" => $this->city,
						"addressRegion" => $this->stateCodeValue,
						"postalCode" => $this->zip,
						"addressCountry" => "US"
					]
				];
			}
			return  $schema;
		} else {
			return null;
		}
	}

	function getLinkJuice() {
		$schema = [];
		$mainLinkJuice = SeoLinkJuice::on('onthefly')
							->select('*')
							->where('service_id',$this->service_id)
							->get()
							->toArray();
		if(!empty($mainLinkJuice)) {
			return $mainLinkJuice[0]['content'];
		} else {
			return null;
		}
	}

	function getService() {
		$schema = [];
		$itemServiceElement = '';
		$providerName = '';
		if($this->type == 'zip') {
			$providerName = ucfirst($this->custom_keyword) .' in '.ucfirst($this->city).' '.$this->zip;
		} else {
			$providerName = ucfirst($this->custom_keyword) .' in '.ucfirst($this->city);
		}
		$servicesData = SeoService::on('onthefly')
							->select('*')
							->where('service_id',$this->service_id)
							->get()
							->toArray();
		if(!empty($servicesData)) {
			$allServiceArray = $this->getServicesArray();
			$itemServiceList = explode(',',$servicesData[0]['services']);
			$schema = [
				'@context'   => "https://schema.org",
				'@type'      => "Service",
				"name" => ucfirst($this->custom_keyword),
				"provider" => [
					"@type" => "Website",
					"name" => $providerName,
				],
				"areaServed" => [
					"@type" => "City",
					"name" => ucfirst($this->city)
				],
				"hasOfferCatalog" => [
					"@type" => "OfferCatalog",
					"name" => ucfirst($this->custom_keyword).' services',
					"itemListElement" => [
						"@type" => "OfferCatalog",
						"name" => $servicesData[0]['name'],
						"itemListElement" => [
							
						],
					],
				],
			];
			foreach($itemServiceList as $row) {
				$itemServiceElement = [
					'@type'   => 'Offer',
					'itemOffered'    => [
						"@type" => "Service",
						"name" => $allServiceArray[$row]
					],
				];
				array_push($schema['hasOfferCatalog']['itemListElement']['itemListElement'], $itemServiceElement); 
			}
		}
		return  $schema;
	}
}
