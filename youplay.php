<?php


  /*
    Plugin Name: YouPlay
    Description: Shortcodes for YouPlay formats
    Author: Robert Brewitz Borg <hello@robertbrewitz.com>
    Version: 1.0.1
    License: MIT
    Ex.:
    [YouPlaySinglePlayer video="334,32,886" yot yod yos ap mute floating="4" time="62" poster="http://poster.com"]
  */

  class YouPlay {
    function YouPlay () {
      $this->__initialize();
    }

    function __initialize () {
      add_shortcode("YouPlayMainPlayer", array(&$this, "main"));
      add_shortcode("YouPlayNewMainPlayer", array(&$this, "newMainPlayer"));
      add_shortcode("YouPlaySinglePlayer", array(&$this, "singlePlayer"));
      add_shortcode("YouPlayLivePlayer", array(&$this, "livePlayer"));
      add_shortcode("YouPlaySingleDisplay", array(&$this, "singleDisplay"));
      add_shortcode("YouPlayColumnDisplay", array(&$this, "columnDisplay"));
      add_shortcode("YouPlayTripleDisplay", array(&$this, "tripleDisplay"));
      add_shortcode("YouPlayVideoLinkDisplay", array(&$this, "videoLinkDisplay"));
      add_shortcode("YouPlayTickerDisplay", array(&$this, "tickerDisplay"));
      add_shortcode("YouPlayMobilePlayer", array(&$this, "mobilePlayer"));
      add_shortcode("YouPlayPlaylistPlayer", array(&$this, "playlistPlayer"));
    }

    function query_params ($attr) {
      $params = array();
      $this->push_param("yot", "1", $params, $attr);
      $this->push_param("yod", "1", $params, $attr);
      $this->push_param("yos", "1", $params, $attr);
      $this->push_param("ap", "true", $params, $attr);
      $this->push_param("mute", "true", $params, $attr);
      $this->push_param("lp", "true", $params, $attr);
      $this->push_param("mv", "true", $params, $attr);
      $this->push_param("dc", "true", $params, $attr);
      $this->push_param("loop", "true", $params, $attr);
      $this->push_param("co", false, $params, $attr);
      $this->push_param("time", false, $params, $attr);
      $this->push_param("floating", false, $params, $attr);
      $this->push_param("poster", false, $params, $attr);
      $this->push_param("pl", false, $params, $attr);
      $this->push_param("nt", false, $params, $attr);
      $this->push_param("sha", false, $params, $attr);
      $this->push_param("ht", "true", $params, $attr);
    $this->push_param("beta_preview", false, $params, $attr);
      return join($params, "&");
    }

    function push_param ($key, $value, &$arr, $attr) {
      if (!$value && array_key_exists($key, $attr)) {
        array_push($arr, "{$key}={$attr[$key]}");
      } else if (in_array($key, $attr)) {
        array_push($arr, "{$key}={$value}");
      }
    }

    function build_path ($param) {
      $str = "";
      $ids = explode(",", $param);
      forEach ($ids as $val) {
        $str .= "/{$val}";
      }
      return $str;
    }

    function get_data_config($attr, $player) {
      $video = explode(",", $attr["video"]);
      $zone_id = $video[0];
      $program_id = $video[1];
      $part_id = $video[2];
      $mute = false;
      $autoplay = false;
      $addons = [0, 0, 0];
      $floating = 2;
      $beta_preview = true;
      $poster = null;

      forEach ($attr as $key => $value) {
        if ($value == "yot") {
          $addons[0] = "1";
        }
        if ($value == "yod") {
          $addons[1] = "1";
        }
        if ($value == "yos") {
          $addons[2] = "1";
        }
        if ($value == "mute") {
          $mute = true;
        }
        if ($value == "ap" || $value == "autoplay") {
          $autoplay = true;
        }
        if ($key === "floating") {
          $floating = $value;
        }
        if ($key === "poster") {
          $poster = $value;
        }
        if ($key === "time") {
          $time = $value;
        }
        if ($value === "hide_list") {
          $hideList = true;
        }
        if ($value === "shuffle") {
          $shuffle = true;
        }
        if ($key === "pl") {
          $pl = $value;
        }
    if ($key === "beta_preview") {
          $beta_preview = $value;
        }
      }

      $data_config = array(
        "zone_id" => intval($zone_id),
        "mute" => $mute,
        "autoplay" => $autoplay,
        "player" => $player
      );

      if ($floating) {
        $data_config["floating"] = $floating;
      }
      if ($beta_preview) {
        $data_config["beta_preview"] = $beta_preview;
      }
      if ($poster) {
        $data_config["poster"] = $poster;
      }

      if ($time) {
        $data_config["time"] = $time;
      }

      if ($program_id) {
        $data_config["program_id"] = intval($program_id);
      }

      if ($player == "sp") {
        $data_config["part_id"] = intval($part_id);
        $data_config["addons"] = implode($addons);
      } elseif ($player == "lp") {
        $data_config["live_stream_id"] = intval($part_id);
      }

      if ($hideList) {
        $data_config["hide_list"] = true;
      }

      if ($shuffle) {
        $data_config["shuffle"] = true;
      }

      if ($pl) {
        $data_config["pl"] = $pl;
      }

      return $data_config;
    }

    function main ($attr) {
      $path = $this->build_path($attr["video"]);
      $params = $this->query_params($attr);
      $str = "<div><script async defer src='//content.youplay.se/expanders/yosemite{$path}.js?{$params}'></script></div>";
      return $str;
    }

    function newMainPlayer ($attr) {
      $link = $attr["link"];
      $str = "<div><script defer async type='text/javascript' charset='utf-8' class='yp-main-player' src='{$link}'></script></div>";
      return $str;
    }

    function singlePlayer ($attr) {
      $data_config = $this->get_data_config($attr, "sp");
      $data_config = json_encode($data_config);
      $rand = rand();
      $str = "<div><script defer async class='yp' src='//delivery.youplay.se/load.js?id={$rand}' data-config='{$data_config}'></script></div>";
      return $str;
    }

    function livePlayer ($attr) {
      $data_config = $this->get_data_config($attr, "lp");
      $data_config = json_encode($data_config);
      $rand = rand();
      $str = "<div><script defer async class='yp' src='//delivery.youplay.se/load.js?id={$rand}' data-config='{$data_config}'></script></div>";
      return $str;
    }

    function singleDisplay ($attr) {
      $path = $this->build_path($attr["video"]);
      $params = $this->query_params($attr);
      $str = "<div><script defer async src='//content.youplay.se/displays/single{$path}.js?{$params}'></script></div>";
      return $str;
    }

    function columnDisplay ($attr) {
      $params = $this->query_params($attr);
      $str = "<div><script defer async src='//content.youplay.se/displays/responsive/{$attr["video"]}.js?{$params}'></script></div>";
      return $str;
    }

    function tripleDisplay ($attr) {
      $params = $this->query_params($attr);
      $str = "<div><script defer async src='//content.youplay.se/displays/triple/{$attr["video"]}.js?{$params}'></script></div>";
      return $str;
    }

    function videoLinkDisplay ($attr) {
      $path = $this->build_path($attr["video"]);
      $params = $this->query_params($attr);
      $str = "<div><script defer async src='//content.youplay.se/displays/video_link{$path}.js?{$params}'></script></div>";
      return $str;
    }

    function tickerDisplay ($attr) {
      $params = $this->query_params($attr);
      $str = "<div><script defer async src='//content.youplay.se/displays/ticker/{$attr["video"]}.js?{$params}'></script></div>";
      return $str;
    }

    function mobilePlayer ($attr) {
      $params = $this->query_params($attr);
      $str = "<div><script defer async src='//content.youplay.se/displays/mobile/{$attr["video"]}.js?{$params}'></script></div>";
      return $str;
    }

    function playlistPlayer ($attr) {
      $data_config = $this->get_data_config($attr, "pp");
      $data_config = json_encode($data_config);
      $rand = rand();
      $str = "<div><script defer async class='yp' src='//delivery.youplay.se/load.js?id={$rand}' data-config='{$data_config}'></script></div>";
      return $str;
    }
  }

  new YouPlay;
?>