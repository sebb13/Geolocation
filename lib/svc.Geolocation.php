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
final class Geolocation extends CoreCommon {
	
	private $oGeolocationMgr = null;
	private $aAllowedFields = array(
								'latitude',
								'longitude',
								'altitude',
								'speed',
								'accuracy',
								'postcode',
								'city',
								'address',
								'id'
							);
	
	public function __construct() {
		parent::__construct();
		$this->oGeolocationMgr = new GeolocationMgr();
		//set data from GET to POST
		foreach(UserRequest::getRequest() as $sKey=>$sValue) {
			if(UserRequest::getParams($sKey) === false && in_array($sKey, $this->aAllowedFields)) {
				UserRequest::setParams($sKey, $sValue);
			}
		}
	}
	
	public function setPosition() {
		return $this->getPosition();
	}
	
	public function getPosition() {
		if(UserRequest::getParams('accuracy') !== false) {
			$this->oGeolocationMgr->iAccuracy = UserRequest::getParams('accuracy');
		} elseif(UserRequest::getParams('latitude') === false || UserRequest::getParams('longitude') === false) {
			$mResult = $this->oGeolocationMgr->getPositionByIP(
											UserRequest::getEnv('REMOTE_ADDR'), 
											true
										);
			UserRequest::setParams('latitude', $mResult['GeoLat']);
			UserRequest::setParams('longitude', $mResult['GeoLon']);
		}
		return json_encode(array(
			'latitude'	=> UserRequest::getParams('latitude'),
			'longitude'	=> UserRequest::getParams('longitude'),
			'altitude'	=> UserRequest::getParams('altitude'),
			'speed'		=> UserRequest::getParams('speed'),
			'accuracy'	=> $this->oGeolocationMgr->iAccuracy
		));
	}
	
	public function getCitiesFromPostCode() {
		$mResult = $this->oGeolocationMgr->getCitiesFromPostCode(
											UserRequest::getParams('postcode')
										);
		if($this->oGeolocationMgr->sOutputFormat === 'array') {
			return $mResult;
		} else {
			die($mResult);
		}
	}
	
	public function getPostCodeFromCity() {
		$mResult = $this->oGeolocationMgr->getPostCodeFromCity(UserRequest::getParams('city'));
		if($this->oGeolocationMgr->sOutputFormat === 'array') {
			return $mResult;
		} else {
			die($mResult);
		}
	}
	
	public function getAddress() {
		$mResult = $this->oGeolocationMgr->getAddress(
											UserRequest::getParams('address'),
											UserRequest::getParams('latitude'), 
											UserRequest::getParams('longitude')
										);
		if($this->oGeolocationMgr->sOutputFormat === 'array') {
			return $mResult;
		} else {
			die($mResult);
		}
	}
	
	public function getAddressFromPosition() {
		$mResult = $this->oGeolocationMgr->getAddressFromPosition(
											UserRequest::getParams('latitude'), 
											UserRequest::getParams('longitude')
										);
		if($this->oGeolocationMgr->sOutputFormat === 'array') {
			return $mResult;
		} else {
			die($mResult);
		}
	}
	
	public function getPositionFromAddress() {
		$mResult = $this->oGeolocationMgr->getPositionFromAddress(
											UserRequest::getParams('address')
										);
		if($this->oGeolocationMgr->sOutputFormat === 'array') {
			return $mResult;
		} else {
			die($mResult);
		}
	}
	
	public function getPositionByIP() {
		$mResult = $this->oGeolocationMgr->getPositionByIP(
											UserRequest::getEnv('REMOTE_ADDR')
										);
		if($this->oGeolocationMgr->sOutputFormat === 'array') {
			return $mResult;
		} else {
			die($mResult);
		}
	}
	
	public function getHomePage() {
		return array(
				'content' => $this->oTplMgr->buildSimpleCacheTpl(
															$this->oGeolocationMgr->getHomePage(), 
															ModulesMgr::getFilePath(__CLASS__, 'backLocales', $this->oLang->LOCALE).'geolocation.xml'
														),
				'sPage'	=> 'geolocation_home'
			);
	}
}