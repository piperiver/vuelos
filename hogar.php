<?php

class consultas{

    var $consultar = [
        [
            "NOMBRE" => "Lavadora",
            "URL" => "https://locomparas.com/producto/lavadora-samsung-activ-dual-wash-wa13j5712lg/",
        ],
        [
            "NOMBRE" => "Nevera",
            "URL" => "https://locomparas.com/producto/nevera-challenger-no-frost-cr430-negra-360lt/"
        ],
        [
            "NOMBRE" => "Nevera",
            "URL" => "https://locomparas.com/producto/nevera-lg-lt32wppx-plateada-333lt/"
        ],
        [
            "NOMBRE" => "Nevera",
            "URL" => "https://locomparas.com/producto/lavadora-samsung-wa18f7l6dda-39lbs-plateada/"
        ],
        [
            "NOMBRE" => "Televisor",
            "URL" => "https://locomparas.com/producto/televisor-lg-49-49lk5700-smart-lcd-full-hd/"
        ],
        [
            "NOMBRE" => "Televisor",
            "URL" => "https://locomparas.com/producto/televisor-lg-49-49uk6200-smart-lcd-4k-uhd/"
        ],
        [
            "NOMBRE" => "Televisor",
            "URL" => "https://locomparas.com/producto/televisor-lg-55-55uk6200-smart-lcd-4k-uhd/"
        ],
        [
            "NOMBRE" => "Televisor",
            "URL" => "https://locomparas.com/producto/televisor-samsung-50-50nu7100-smart-led-4k-uhd/"
        ],
        [
            "NOMBRE" => "Televisor",
            "URL" => "https://locomparas.com/producto/televisor-samsung-50-un50mu6103-smart-4k-uhd/"
        ],
        [
            "NOMBRE" => "Televisor",
            "URL" => "https://locomparas.com/producto/televisor-samsung-55-55nu7100-smart-led-4k-uhd/"
        ],
        [
            "NOMBRE" => "Televisor",
            "URL" => "https://locomparas.com/producto/televisor-samsung-58-58nu7100-smart-led-4k-uhd/"
        ],
    ];

    function index(){
        
        $data = $this->getData();
        return $this->pintar($data);

        
    }

    function pintar($data){

        $html = "<h1>".date("d-m-Y")."</h1>";
        foreach($data as $item){
            $li = "";
            foreach($item["precios"] as $precio){
                $li .= "<li>
                            <div>".$precio["tienda"]."</div>
                            <div><strong>".$precio["precio"]."</strong></div>
                            <br>
                        </li>";
            }


            $html .= "
                <div>
                    <h2>".$item["titulo"]."</h2>
                    <a href=".$item["direccion"]." target=".'_blank'.">".$item["direccion"]."</a>s
                    <ul><strong>PRECIOS:</strong><br><br>
                        $li      
                    </ul>
                </div>
            ";
        }
        return $html;
    }

    function getData(){
        $data = [];

        foreach($this->consultar as $consultar){
            $document = $this->getHtml($consultar["URL"]);
            $titulo =  $this->extraerByTag("title", $document);
            $precios = $this->extraerByClass("val_sim_price", $document);
            $textos = $this->extraerByClass("vendor_sim_price", $document);
            
            $tempData = [];
            $tempData["titulo"] = $titulo;
            $tempData["direccion"] = $consultar["URL"];
            for ($i=0; $i < count($textos); $i++) { 
                $tempData["precios"][]= [
                    "tienda" => $textos[$i],
                    "precio" => isset($precios[$i])? $precios[$i] : "No Reporta"
                ];
            }
            $data[] = $tempData;
        }
        return $data;
    }

    function getHtml($url){
        return file_get_contents($url);
    }

    function extraerByClass($classname, $html){
        $dom = new DomDocument();
        $dom->loadHTML($html);
        $finder = new DomXPath($dom);
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

        $response = [];
        foreach($nodes as $node){
            $response[] = trim(preg_replace("/[\r\n]+/", " ", $node->nodeValue));
        }
        return $response;
    }

    function extraerByTag($tag, $html){
        $dom = new DomDocument();
        $dom->loadHTML($html);
        $title = $dom->getElementsByTagName($tag)->item(0);
        $text = trim(preg_replace("/[\r\n]+/", " ", $title->nodeValue));
        return $text;
    }

}







