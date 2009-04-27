<?
/*  Copyright (C) 2008 Robb Shecter ( greenfabric.com )

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA */

require_once "HTTP/Request.php";

class BBIntegrationApi {
  public $server_path;
  public $cached_config_info = false;

  public function __construct($url) {
    $this->server_path = $url;
  }

  //------------- Public API ---------------

  public function is_logged_in() {
    return ! ($this->user_info() == NULL);
  }
  
  public function user_info() {
    if ($this->rails_cookie_value() == NULL)
      return NULL;
    $json_data = $this->api_request("user/" . $this->rails_cookie_value());
    return $json_data->{'user'};
  }

  public function login_url() {
    return $this->config_info()->{'login_url'};
  }

  public function logout_url() {
    return $this->config_info()->{'logout_url'};
  }
  

  //------------- Private methods -------------

  function rails_cookie_value() {
    return $_COOKIE[$this->rails_cookie_name()];
  }
  
  function rails_cookie_name() {
    return $this->config_info()->{'cookie_name'};
  }

  function config_info() {
    if (! $this->cached_config_info)
      $this->cached_config_info = $this->api_request("config_info");
    return $this->cached_config_info;
  }  
  
  function api_request($query) {
    $r =& new HTTP_Request($this->server_path . $query);
    $r->sendRequest();
    return json_decode($r->getResponseBody());
  }

}
?>