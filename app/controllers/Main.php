<?php

/**
 * WPanel CMS
 *
 * An open source Content Manager System for websites and systems using CodeIgniter.
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2008 - 2017, Eliel de Paula.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     WpanelCms
 * @author      Eliel de Paula <dev@elieldepaula.com.br>
 * @copyright   Copyright (c) 2008 - 2017, Eliel de Paula. (https://elieldepaula.com.br/)
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://wpanel.org
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Main Controller Class
 *
 * This class maintain the methods of the basic website. It was thought that
 * you add more resources to your project creating new Controller Classes
 * extending MY_Controller Class to get the common features.
 *
 * @package     WpanelCms
 * @subpackage  Controllers
 * @category    Controllers
 * @author      Eliel de Paula <dev@elieldepaula.com.br>
 * @extends     MY_Controller
 * @since       v1.0.0
 */
class Main extends MY_Controller
{

    /**
     * Class constructor.
     *
     * @return void
     */
    function __construct()
    {

        /**
         * Here are some options provided by the MY_Controller class, you
         * can adjust as you need to your project.
         */
        /**
         * Enable the CodeIgniter Profile.
         */
        // $this->show_profiler = TRUE;

        /**
         * Set the 'col' number of the mosaic views.
         */
        $this->wpn_cols_mosaic = 3;

        /**
         * Set the default post view: list (default) or mosaic.
         */
        $this->wpn_posts_view = 'mosaic';

        parent::__construct();
        $this->wpanel->check_setup();
    }

    /**
     * You can use this method to create 'custom' home page to your site and
     * then select the 'custom' page in the admin configuration panel.
     *
     * @return void
     */
    public function custom()
    {
        $this->wpanel->set_meta_title('Início');
        $this->view('main/custom')->render();
    }

    /**
     * The method index() select the configured home page of the site.
     *
     * @return void
     */
    public function index()
    {
        switch (wpn_config('home_tipo'))
        {
            case 'page':
                $this->post(wpn_config('home_id'), true);
                break;
            case 'category':
                $this->posts(wpn_config('home_id'));
                break;
            default:
                return $this->custom();
                break;
        }
    }

    /**
     * The method posts() retuns a list of posts, it can be categoryzed and
     * can be a list or mosaic view.
     *
     * @param $category_id Int Category ID.
     * @return void
     */
    public function posts($category_id = null)
    {
        $view_title = '';
        $view_description = '';
        $this->load->model('post');
        $this->load->model('categoria');
        // Check if is a categoryzed list.
        if ($category_id == null)
        {
            $this->set_var('posts', $this->post->order_by('created_on', 'desc')->find_many_by(array('page' => '0', 'status' => '1')));
            $view_title = 'Todas as postagens';
        } else
        {
            $qry_category = $this->categoria->find($category_id);
            $this->set_var('posts', $this->post->get_by_category($category_id, 'desc')->result());
            $this->set_var('view_title', $qry_category->title);
            $this->set_var('view_description', $qry_category->description);
            $view_title = $qry_category->title;
            $this->wpn_posts_view = strtolower($qry_category->view);
        }
        // Send $max_cols if the view is mosaic type.
        if ($this->wpn_posts_view == 'mosaic')
            $this->set_var('max_cols', $this->wpn_cols_mosaic);
        $this->wpanel->set_meta_title($view_title);
        $this->view('main/posts_' . $this->wpn_posts_view)->render();
    }

    /**
     * The method post() shows a post by link or by ID if $use_id = True.
     *
     * @param $link mixed Link or ID field of the post.
     * @param $use_id boolean Indicates if $link is a ID.
     * @return void
     */
    public function post($link = '', $use_id = false)
    {
        if ($link == '')
            show_404();
        $this->load->model('post');
        if ($use_id)
            $query = $this->post->find($link);
        else
            $query = $this->post->find_by('link', $link);
        $this->set_var('post', $query);
        if (count($query) <= 0)
            show_404();
        if ($query->status == 0)
            show_error('Esta página foi suspensa temporariamente', 404);
        $this->wpanel->set_meta_description($query->description);
        $this->wpanel->set_meta_keywords($query->tags);
        $this->wpanel->set_meta_title($query->title);
        if (file_exists('./media/capas/' . $query->image))
            $this->wpanel->set_meta_image(base_url('media/capas/' . $query->image));
        // Select the spacific type of view according to type of post.
        switch ($query->page)
        {
            case '1':
                $this->view('main/page')->render();
                break;
            case '2':
                $this->view('main/event')->render();
                break;
            default:
                $this->view('main/post')->render();
                break;
        }
    }

    /**
     * The method events() shows a list of posts typed as 'event'.
     *
     * @return void
     */
    public function events()
    {
        $view_title = 'Eventos';
        $this->load->model('post');
        $query = $this->post->order_by('created_on', 'desc')->find_many_by(array('page' => '2', 'status' => '1'));
        $this->wpanel->set_meta_title($view_title);
        $this->wpanel->set_meta_description('Lista de eventos');
        $this->wpanel->set_meta_keywords(' eventos, agenda');
        $this->set_var('events', $query);
        $this->render();
    }

    /**
     * The method search() make a simple search function into the Posts.
     *
     * @todo Melhorar a view de resultados usando um estilo de tabela.
     * @return void
     */
    public function search()
    {
        $search_terms = $this->input->post('search');
        $this->load->model('post');
        $this->set_var('search_terms', $search_terms);
        $this->set_var('results', $this->post->busca_posts($search_terms)->result());
        $this->wpanel->set_meta_title('Resultados da busca por ' . $search_terms);
        $this->render();
    }

    /**
     * The method albuns() list all the available galeries of the site.
     *
     * @return void
     */
    public function galleries()
    {
        $this->load->model('gallery');
        $query = $this->gallery->order_by('created_on', 'desc')->find_many_by('status', 1);
        $this->wpanel->set_meta_description('Álbuns de fotos');
        $this->wpanel->set_meta_keywords(' album, fotos');
        $this->wpanel->set_meta_title('Álbuns de fotos');
        $this->set_var('albuns', $query);
        $this->set_var('max_cols', $this->wpn_cols_mosaic);
        $this->render();
    }

    /**
     * The method album() shows a list of pictures of a galery selected by $album_id.
     *
     * @param $album_id Int ID of the galery.
     * @return void
     */
    public function gallery($album_id = null)
    {
        if ($album_id == null)
            show_404();
        $this->load->model('gallery');
        $this->load->model('picture');
        $query_album = $this->gallery->find($album_id);
        if (count($query_album) <= 0)
            show_404();
        if ($query_album->status == 0)
            show_error('Este álbum foi suspenso temporariamente', 404);
        $query_pictures = $this->picture->find_many_by(array('album_id' => $album_id, 'status' => 1));
        $this->wpanel->set_meta_description($query_album->descricao);
        $this->wpanel->set_meta_keywords(' album, fotos');
        $this->wpanel->set_meta_title($query_album->titulo);
        if (file_exists('./media/capas/' . $query_album->capa))
            $this->wpanel->set_meta_image(base_url('media/capas' . '/' . $query_album->capa));
        $this->set_var('album', $query_album);
        $this->set_var('pictures', $query_pictures);
        $this->set_var('max_cols', $this->wpn_cols_mosaic);
        $this->render();
    }

    /**
     * The method foto() shows the picture selected by $picture_id, it only works
     * if you are not using the lightbox plugin.
     *
     * @param $picture_id Int Id of the picture.
     * @return void
     * @deprecated since version 4.0
     */
    public function picture($picture_id = null)
    {
        if ($picture_id == null)
            show_404();
        $this->load->model('gallery');
        $this->load->model('picture');
        $query_picture = $this->picture->find($picture_id);
        $query_album = $this->gallery->find($query_picture->album_id);
        if (count($query_picture) <= 0)
            show_404();
        if ($query_picture->status == 0)
            show_error('Esta foto foi suspensa temporariamente', 404);
        $this->wpanel->set_meta_description($query_picture->descricao);
        $this->wpanel->set_meta_keywords('album, fotos');
        $this->wpanel->set_meta_title($query_picture->descricao);
        if (file_exists('./media/albuns/' . $query_picture->album_id . '/' . $query_picture->filename))
            $this->wpanel->set_meta_image(base_url('media/albuns/' . $query_picture->album_id . '/' . $query_picture->filename));
        $this->set_var('album', $query_album);
        $this->set_var('picture', $query_picture);
        $this->render();
    }

    /**
     * The method videos() shows a list of videos from youtube. The videos is not
     * loaded automaticaly from the channel, it must be inserted into the control
     * panel by the manager.
     *
     * @return void
     */
    public function videos()
    {
        $this->load->model('video');
        $query_videos = $this->video->order_by('created_on', 'desc')->find_many_by('status', 1);
        $this->wpanel->set_meta_description('Lista de vídeos');
        $this->wpanel->set_meta_keywords('videos, filmes');
        $this->wpanel->set_meta_title('Vídeos');
        $this->set_var('videos', $query_videos);
        $this->set_var('max_cols', $this->wpn_cols_mosaic);
        $this->render();
    }

    /**
     * The method video() shows a video selected by $code.
     *
     * @param $code string Youtube code for the video.
     * @return void
     * @deprecated since version 4.0
     */
    public function video($code = null)
    {
        if ($code == null)
            show_404();
        $this->load->model('video');
        $query_video = $this->video->find_by(array('link' => $code, 'status' => 1));
        if (count($query_video) <= 0)
            show_404();
        if ($query_video->status == 0)
            show_error('Este vídeo foi suspenso temporariamente', 404);
        $this->data_content['video'] = $query_video;
        $this->wpanel->set_meta_description($query_video->titulo);
        $this->wpanel->set_meta_keywords('videos, filmes');
        $this->wpanel->set_meta_title($query_video->titulo);
        $this->wpanel->set_meta_image('http://img.youtube.com/vi/' . $code . '/0.jpg');
        $this->render('video');
    }

    /**
     * The method contact() creates a full functional 'Contact Page' for the site.
     *
     * @todo Criar a opção de inserir a mensagem no banco de dados e no painel de contorle.
     * @return void
     */
    public function contact()
    {
        $this->form_validation->set_rules('nome', 'Nome', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('captcha', 'Confirmação', 'required|captcha');
        $this->form_validation->set_error_delimiters('<p><span class="label label-danger">', '</span></p>');
        if ($this->form_validation->run() == FALSE)
        {
            $this->wpanel->set_meta_description('Formulário de contato');
            $this->wpanel->set_meta_keywords(' Contato, Fale Conosco');
            $this->wpanel->set_meta_title('Contato');
            $this->set_var('contact_content', wpn_config('texto_contato'));
            $this->set_var('captcha', $this->form_validation->get_captcha());
            $this->render();
        } else
        {
            // Receive the values of the form.
            $nome = $this->input->post('nome');
            $email = $this->input->post('email');
            $telefone = $this->input->post('telefone');
            $mensagem = $this->input->post('mensagem');
            // Make a message string.
            $msg = "";
            $msg .= "Mensagem enviada pelo site.\n\n";
            $msg .= "Nome: $nome\n";
            $msg .= "Email: $email\n";
            $msg .= "Telefone: $telefone\n";
            $msg .= "IP: " . $this->input->server('REMOTE_ADDR', true) . "\n\n";
            $msg .= "Mensagem\n";
            $msg .= "------------------------------------------------------\n\n";
            $msg .= "$mensagem";
            $msg .= "\n\n";
            $msg .= "Enviado pelo WPanel CMS\n";
            $mail_data = array(
                'html' => FALSE,
                'from_name' => $nome,
                'from_email' => $email,
                'to' => wpn_config('site_contato'),
                'subject' => 'Formulário de contato - ' . wpn_config('site_titulo'),
                'message' => $msg,
            );
            if ($this->wpanel->send_email($mail_data))
                $this->set_message('Sua mensagem foi enviada com sucesso!', 'success', 'contact');
            else
                $this->set_message('Sua mensagem não pode ser enviada.', 'danger', 'contact');
        }
    }

    /**
     * The method rss() creates a XML page to Feed Readers with a list of posts.
     *
     * @todo Criar o metodo de categorizar esta lista de feed.
     * @return void
     */
    public function rss()
    {
        $this->load->model('post');
        $query = $this->post->order_by('created_on', 'desc')->find_all();
        $available_languages = config_item('available_languages');
        $locale = $available_languages[wpn_config('language')]['locale'];
        $rss = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $rss .= "<rss version=\"2.0\">\n";
        $rss .= "\t<channel>\n";
        $rss .= "\t\t<title>" . wpn_config('site_titulo') . "</title>\n";
        $rss .= "\t\t<description>" . wpn_config('site_desc') . "</description>\n";
        $rss .= "\t\t<link>" . site_url() . "</link>\n";
        $rss .= "\t\t<language>" . $locale . "</language>\n";
        foreach ($query as $row)
        {
            $rss .= "\t\t<item>\n";
            $rss .= "\t\t\t<title>" . $row->title . "</title>\n";
            $rss .= "\t\t\t<description>" . $row->description . "</description>\n";
            $rss .= "\t\t\t<lastBuildDate>" . $row->created_on . "</lastBuildDate>\n";
            $rss .= "\t\t\t<link>" . site_url('post/' . $row->link) . "</link>\n";
            $rss .= "\t\t</item>\n";
        }
        $rss .= "\t</channel>\n</rss>\n";
        echo $rss;
    }

    /**
     * The method newsletter() show a form to insert contact for newsletter.
     *
     * @todo Enviar uma mensagem de confirmação do cadastro para o email.
     * @return void
     */
    public function newsletter()
    {
        $this->form_validation->set_rules('nome', 'Nome', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_error_delimiters('<p><span class="label label-danger">', '</span></p>');
        if ($this->form_validation->run() == FALSE)
        {
            $this->wpanel->set_meta_description('Newsletter');
            $this->wpanel->set_meta_keywords('Cadastro, Newsletter');
            $this->wpanel->set_meta_title('Newsletter');
            $this->render();
        } else
        {
            $this->load->model('newsletter');
            $data = array(
                'nome' => $this->input->post('nome', true),
                'email' => $this->input->post('email', true),
                'ipaddress' => $this->input->server('REMOTE_ADDR', true)
            );
            if ($this->newsletter->insert($data))
                $this->set_message('Seus dados foram salvos com sucesso!', 'success', 'newsletter');
            else
                $this->set_message('Não foi possível salvar os seus dados, verifique e tente novamente.', 'danger', 'newsletter');
        }
    }

}
