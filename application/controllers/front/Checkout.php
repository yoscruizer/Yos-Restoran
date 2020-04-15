<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Checkout extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('template_front');
    }

    public function index()
    {
        redirect('checkout/review');
    }

    public function review()
    {
        $cart_content = $this->cart->contents();

        if ($cart_content) {
            $total_qty   = 0;
            $total_waktu = 0;
            foreach ($cart_content as $key => $value) {
                $waktu                          = $value['waktu'];
                $qty                            = $value['qty'];
                $cart_content[$key]['waktu']    = $waktu;
                $cart_content[$key]['qty']      = $qty;
                $cart_content[$key]['subtotal'] = number_format($value['price'] * $value['qty'], 0, '', ',');
                $total_qty += $qty;
                $total_waktu += $waktu;
            }

            $data['total_waktu']             = $total_waktu;
            $data['total_qty']               = $total_qty;
            $this->data['cart_count']        = count($cart_content);
            $this->data['total_waktu']       = $total_waktu;
            $this->data['total_qty']         = $total_qty;
            $this->data['cart_content']      = $cart_content;
            $this->data['cart_total']        = $this->cart->total();
            $this->data['cart_total_format'] = number_format($this->cart->total(), 0, '', '.');
            $this->data['resto_meja']        = $this->db->get('resto_meja')->result();
            $this->data['cart_total']        = $this->cart->total();
            $data['review_content']          = $this->parser->parse('front/checkout/review_content.html', $this->data, true);
            $this->template_front->display('front/checkout/review', $data);
        } else {
            redirect('/cart');
        }
    }

    // public function dobooking()
    // {
    //     if ($this->input->post()) {
    //         $resto_order        = json_decode($this->input->post('resto_order'), true);
    //         $resto_order_detail = json_decode($this->input->post('resto_order_detail'), true);
    //         $this->db->insert('resto_order', $resto_order);
    //         $order_id = $this->db->insert_id();
    //         $res             = array();
    //         $res['order_id'] = $order_id;
    //         foreach ($resto_order_detail as $key => $value) {
    //             $value['order_id'] = $order_id;
    //             $this->db->insert('resto_order_detail', $value);
    //         }
    //         $this->cart->destroy();
    //         if ($this->input->is_ajax_request()) {
    //             header('Content-Type: application/json');
    //             echo json_encode($res);
    //         } else {
    //             opn($res);
    //         }
    //     }
    // }

    public function konfirmasi()
    {
        if ($this->input->post()) {
            $param             = $this->input->post(null, true);
            $param_resto_order = $param['resto_order'];
            $dataOrder         = array(
                'meja_id'       => stripHTMLtags($param['resto_order']['meja_id']),
                'order_nama'    => strtoupper(stripHTMLtags(trim($param['resto_order']['nama']))),
                'order_tanggal' => date('Y-m-d'),
                'order_waktu'   => $param['resto_order']['total_waktu'],
                'order_qty'     => $param['resto_order']['total_qty'],
                'order_catatan' => $param['resto_order']['catatan'],
                'order_total'   => $param['resto_order']['total'],
                'order_update'  => date('Y-m-d H:i:s'),
            );

            $this->db->insert('resto_order', $dataOrder);
            $order_id     = $this->db->insert_id();
            $cart_content = $this->cart->contents();
            foreach ($cart_content as $key => $value) {
                $subtotal   = ($value['qty'] * $value['price']);
                $dataDetail = array(
                    'order_id'              => $order_id,
                    'menu_id'               => $value['id'],
                    'order_detail_harga'    => $value['price'],
                    'order_detail_qty'      => $value['qty'],
                    'order_detail_waktu'    => $value['waktu'],
                    'order_detail_subtotal' => $subtotal,
                    'order_detail_update'   => date('Y-m-d H:i:s'),
                );

                $this->db->insert('resto_order_detail', $dataDetail);
            }

            $res['order_id'] = $order_id;
            $this->cart->destroy();
            if ($this->input->is_ajax_request()) {
                header('Content-Type: application/json');
                echo json_encode($res);
            } else {
                opn($res);
            }
        }
    }

    public function selesai($order_id = null)
    {
        // $this->db->where('order_id', $order_id);
        // $resto_order = $this->db->get('resto_order')->result();
        // if ($resto_order) {
        //     foreach ($resto_order as $key => $value) {
        //         $this->db->where('order_id', $value->order_id);
        //         $this->db->join('resto_menu', 'resto_menu.menu_id = resto_order_detail.menu_id', 'left');
        //         $value->resto_order_detail = $this->db->get('resto_order_detail')->result();
        //     }
        //     $this->data['resto_order'] = $resto_order;
        //     $data['detail_content']    = $this->parser->parse('front/checkout/detail_content.html', $this->data, true);
        // } else {
        //     $data['detail_content'] = $this->parser->parse('front/checkout/detail_content_empty.html', $this->data, true);
        // }
        // $data['class'] = 'woocommerce-checkout';

        $data['Order']     = $this->db->get_where('v_order', array('order_id' => $order_id))->row();
        $data['listOrder'] = $this->db->get_where('v_order_detail', array('order_id' => $order_id))->result();
        $this->template_front->display('front/checkout/detail_v', $data);
    }

}
/* Location: ./application/controller/front/Checkout.php */
