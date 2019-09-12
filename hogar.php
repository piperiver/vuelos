<?php

class consultas{

    var $consultar = [
        [
            "NOMBRE" => "Lavadora Samsung",
            "URL" => "https://locomparas.com/producto/lavadora-samsung-activ-dual-wash-wa13j5712lg/",
        ],
        [
            "NOMBRE" => "Nevera",
            "URL" => "https://locomparas.com/producto/nevecon-haceb-sbs656l-titanio-656lt/"
        ]
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
            $titulo =  $this->extraerByTag($consultar["URL"], "title");
            $precios = $this->extraerByClass($consultar["URL"], "val_sim_price");
            $textos = $this->extraerByClass($consultar["URL"], "vendor_sim_price");
            
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
        echo "<pre>";
        print_r($data);
    }

    function extraerByClass($url, $classname){
        $html = file_get_contents($url);

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

    function extraerByTag($url, $tag){
        $html = file_get_contents($url);

        $dom = new DomDocument();
        $dom->loadHTML($html);
        $title = $dom->getElementsByTagName($tag)->item(0);
        $text = trim(preg_replace("/[\r\n]+/", " ", $title->nodeValue));
        return $text;
    }

}







