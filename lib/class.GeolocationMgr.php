<?php
/*
 *	minim - PHP framework
    Copyright (C) 2019  Sébastien Boulard

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; see the file COPYING. If not, write to the
    Free Software Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */
final class GeolocationMgr extends SimpleXmlMgr {
	
	private $sAdresseGouvSearchApiUrl = 'https://api-adresse.data.gouv.fr/search/?q={__QUERY__}';
	private $sAdresseGouvReverseApiUrl = 'https://api-adresse.data.gouv.fr/reverse/?lat={__LAT__}&lon={__LON__}';
	private $sLatLonQuery = '&lat={__LAT__}&lon={__LON__}';
	private $sLocateByIpUrl = 'https://www.iplocate.io/api/lookup/{__IP__}';
	private $sConfInterface = '';
	public $iAccuracy = 0;
	public $sOutputFormat = '';
	public static $sModuleName = 'Geolocation';
	
	public function __construct() {
		parent::__construct();
		//return data format
		$oConfig = new Config(self::$sModuleName);
		$this->sOutputFormat = $oConfig->getGlobalConf('FORMAT');
		$this->sConfInterface = $oConfig->getConfInterface(self::$sModuleName, 'Geolocation::getHomePage');
		unset($oConfig);
	}
	
	public function getCitiesFromPostCode($sPostCode) {
		$sUrl = str_replace(
			'{__QUERY__}', 
			$sPostCode, 
			$this->sAdresseGouvSearchApiUrl
		);
		$mResult = $this->curlSend($sUrl, false);
		$aOut = array();
		$aResult = (array)json_decode($mResult);
		foreach($aResult['features'] as $oFeatures) {
			$aOut[] = array(
				'PropCity'		=> urldecode(current((array)$oFeatures->properties->city)),
				'PropId'		=> current((array)$oFeatures->properties->id)
			);
		}
		return $this->returnFormat($aOut);
	}
	
	public function getPostCodeFromCity($sCity) {
		$sUrl = str_replace(
			'{__QUERY__}', 
			urlencode($sCity), 
			$this->sAdresseGouvSearchApiUrl
		);
		$mResult = $this->curlSend($sUrl, false);
		$aOut = array();
		$aResult = (array)json_decode($mResult);
		foreach($aResult['features'] as $oFeatures) {
			$aOut[] = array(
				'PropPostCode'	=> current((array)$oFeatures->properties->postcode),
				'PropCityCode'	=> current((array)$oFeatures->properties->citycode),
				'PropId'		=> current((array)$oFeatures->properties->id)
			);
		}
		return $this->returnFormat($aOut);
	}
	
	public function getAddress($sAddress, $sLat='', $sLon='') {
		$sUrl = str_replace(
			'{__QUERY__}', 
			urlencode($sAddress), 
			$this->sAdresseGouvSearchApiUrl
		);
		if(!empty($sLat) && !empty($sLon)) {
			$sUrl .= str_replace(
				array('{__LAT__}','{__LON__}'),
				array($sLat, $sLon),
				$this->sLatLonQuery
			);
		}
		return $this->returnFormat($this->curlSend($sUrl));
	}
	
	public function getPositionFromAddress($sAddress) {
		$sUrl = str_replace(
			'{__QUERY__}', 
			urlencode($sAddress), 
			$this->sAdresseGouvSearchApiUrl
		);
		return $this->returnFormat($this->curlSend($sUrl));
	}
	
	public function getAddressFromPosition($sLat, $sLon) {
		$sUrl = str_replace(
			array('{__LAT__}','{__LON__}'),
			array($sLat, $sLon),
			$this->sAdresseGouvReverseApiUrl
		);
		return $this->returnFormat($this->curlSend($sUrl));
	}
	
	/*
	 * Nous nous appuyons ici sur la version gratuite de l'API
	 * www.iplocate.io
	 * à vous de l'utiliser ou non, voir de changer d'API pour un système plus durable
	 */
	public function getPositionByIP($sIP, $bInArray=false) {
		$sUrl = str_replace('{__IP__}', $sIP, $this->sLocateByIpUrl);
		$aPosition = (array)json_decode(file_get_contents($sUrl));
		$aReturn = array(
						'GeoLon'		=> $aPosition['longitude'],
						'GeoLat'		=> $aPosition['latitude'],
						'PropPostCode'	=> $aPosition['postal_code'],
						'PropCity'		=> $aPosition['city'],
					);
		return $bInArray ? $aReturn : $this->returnFormat($aReturn);
	}
	
	private function curlSend($sUrl, $bFormat=true) {
		$aDefaults = array( 
			CURLOPT_HEADER => 0,
			CURLOPT_URL => $sUrl,
			CURLOPT_RETURNTRANSFER => 1
		); 
		$rCh = curl_init(); 
		curl_setopt_array($rCh, $aDefaults);
		$mResult = curl_exec($rCh);
		$http_status = curl_getinfo($rCh, CURLINFO_HTTP_CODE);
		if(empty($mResult)) {
			return $http_status.' '.curl_error($rCh);
		}
		curl_close($rCh);
		return $bFormat ? $this->dataFormat($mResult) : $mResult;
	}
	
	private function dataFormat($mResult) {
		$aOut = array();
		$aResult = (array)json_decode($mResult);
		foreach($aResult['features'] as $oFeatures) {
			$aOut[] = array(
				'GeoLon'		=> current((array)$oFeatures->geometry->coordinates[0]),
				'GeoLat'		=> current((array)$oFeatures->geometry->coordinates[1]),
				'GeoType'		=> current((array)$oFeatures->properties->type),
				'PropName'		=> current((array)$oFeatures->properties->name),
				'PropCityCode'	=> current((array)$oFeatures->properties->citycode),
				'PropPostCode'	=> current((array)$oFeatures->properties->postcode),
				'PropCity'		=> current((array)$oFeatures->properties->city),
				'PropContext'	=> current((array)$oFeatures->properties->context),
				'PropScore'		=> current((array)$oFeatures->properties->score),
				'PropImportance'=> current((array)$oFeatures->properties->importance),
				'PropId'		=> current((array)$oFeatures->properties->id),
				'PropAccuracy'	=> $this->iAccuracy
			);
		}
		return $aOut;
	}
	
	private function returnFormat(array $aResult) { //factory
		switch($this->sOutputFormat) {
			case 'json':
				return json_encode($aResult);
			case 'XML':
				return $this->array2xml($this->getEmptyXmlObject('data'), $aResult)->asXML();
			case 'array':
				return $aResult;
			default:
				throw new GenericException('invalid format: '.$this->sOutputFormat);
		}
	}
	
	public function getHomePage() {
		return str_replace(
						'{__CONFIG__}',
						$this->sConfInterface,
						file_get_contents(ModulesMgr::getFilePath(self::$sModuleName, 'backContentsTpl').'home.tpl')
					);
	}
}