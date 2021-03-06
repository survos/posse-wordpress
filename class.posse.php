<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;
use Symfony\Component\DependencyInjection\ContainerInterface;

// registration related functions
require_once(POSSE__PLUGIN_DIR.'inc/comments.php');
require_once(POSSE__PLUGIN_DIR.'inc/registration.php');
require_once(POSSE__PLUGIN_DIR.'inc/post-types.php');
require_once(POSSE__PLUGIN_DIR.'inc/custom-fields.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/ct.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/user.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/memberships.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/membership.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/job.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/jobs.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/register.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/assignment.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/assignments.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/iframe.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/survey.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/surveys.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/my-projects.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/my-tracks.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/my-assignments.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/my-waves.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/projects.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/project_attribute.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/posse_carto_map.php');

require_once(POSSE__PLUGIN_DIR.'shortcodes/login-form.php');
require_once(POSSE__PLUGIN_DIR.'shortcodes/user-calendar.php');

/**
 * Class Posse
 */
class Posse
{
    private static $initiated = false;

    public static function install()
    {

    }

    public static function init()
    {
        if (!self::$initiated) {
            self::init_hooks();
        }
    }

    /**
     * Initializes WordPress hooks
     */
    private static function init_hooks()
    {
        self::$initiated = true;
//        add_filter('query_vars', ['Posse', 'posse_custom_query_vars']);
//        add_filter('rewrite_rules_array', ['Posse', 'posse_theme_functionality_urls']);
        self::initSymfony();

        add_shortcode('assignment', 'posse_assignment');
        add_shortcode('assignments', 'posse_assignments');
        add_shortcode('cartomap', 'posse_carto_map');
        add_shortcode('project', 'posse_project_attribute');
        add_shortcode('my-projects', 'my_posse_projects');
        add_shortcode('my-tracks', 'my_posse_tracks');
        add_shortcode('my-assignments', 'my_posse_assignments');
        add_shortcode('my-waves', 'my_posse_waves');
        add_shortcode('projects', 'posse_projects');
        add_shortcode('jobs', 'posse_jobs');
        add_shortcode('job', 'posse_job');
        add_shortcode('surveys', 'posse_surveys');
        add_shortcode('survey', 'posse_survey');
        add_shortcode('register', 'posse_register');
        add_shortcode('ct', 'posse_ct');
        add_shortcode('user', 'posse_user');
        add_shortcode('iframe', 'posse_iframe');
        add_shortcode('twig', 'posse_user');
        add_shortcode('memberships', 'posse_memberships');
        add_shortcode('membership', 'posse_membership');
        add_shortcode('login-form', 'posse_login_form');
        add_shortcode('user-calendar', 'posse_user_calendar');

        add_action('wp_enqueue_scripts', ['Posse', 'load_assets']);

        add_action('wp_signup_location', 'posse_register_add_project_code');

        // register custom post types
        posse_create_post_types();

        // register ACF (custom fields)
        posse_create_custom_fields();

        add_action("wp_logout", "posse_logout");

        // disable comments
        add_action('admin_init', 'df_disable_comments_post_types_support');
        add_filter('comments_open', 'df_disable_comments_status', 20, 2);
        add_filter('pings_open', 'df_disable_comments_status', 20, 2);
        add_filter('comments_array', 'df_disable_comments_hide_existing_comments', 10, 2);
        add_action('admin_menu', 'df_disable_comments_admin_menu');
        add_action('admin_init', 'df_disable_comments_admin_menu_redirect');
        add_action('admin_init', 'df_disable_comments_dashboard');
        add_action('init', 'df_disable_comments_admin_bar');

        function posse_register_add_project_code($link)
        {
            $site = get_blog_details();
            $parts = explode('.', $site->domain);
            $projectCode = reset($parts);

            return $link."?project=".$projectCode;
        }

    }

    /**
     *
     */
    public static function logoutAll()
    {
//        $token = new \Symfony\Component\Security\Core\Authentication\Token\AnonymousToken('fos_userbundle', 'anon.');
//        self::symfony('security.token_storage')->setToken($token);
//        $session = self::symfony('request_stack')->getCurrentRequest()->getSession();
//        $session->invalidate();
    }

    /**
     * load full calendar styles + deps
     */
    public static function load_calendar_assets()
    {
        /*
        wp_enqueue_style('fullcalendar', '/components/fullcalendar/fullcalendar.css');
//        wp_enqueue_style('fullcalendar-print', '//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.3.0/fullcalendar.print.css');
        wp_enqueue_script('fullcalendar', '/components/fullcalendar/fullcalendar.js', ['jquery', 'moment-tz']);
        */
        // main plugin assets
        wp_enqueue_script('moment', '/components/moment/moment.js');
        wp_enqueue_script('moment-tz', '/components/moment-timezone/moment-timezone-with-data-2010-2020.min.js', ['moment']);
    }

    /**
     * load plugin assets
     */
    public static function load_assets()
    {
        wp_enqueue_script('posse-main', plugin_dir_url(__FILE__) . 'js/main.js');
        wp_enqueue_style('posse-main', plugin_dir_url(__FILE__) . 'css/main.css');
        wp_enqueue_script('torque', "//cartodb.github.io/torque/dist/torque.full.js");
        wp_enqueue_script('cartodb', "//cartodb-libs.global.ssl.fastly.net/cartodb.js/v3/3.14/cartodb.js");

        wp_enqueue_style('cartodb', "//cartodb-libs.global.ssl.fastly.net/cartodb.js/v3/3.14/themes/css/cartodb.css");
    }

    public static function syncUser(WP_User $user, $password = '')
    {
        /** @var \Posse\UserBundle\Manager\UserManager $um */
        $um = self::symfony('survos.user_manager');

        $um->createUserFromWp($user, $password);

    }

    /**
     * fixing magic quotes added by wordpress
     * @param $content
     *
     * @return mixed
     */
    public static function fixContentQuotes($content)
    {
        $content = str_replace("‘",'"', $content);
        $content = str_replace("’",'"', $content);
        $content = str_replace("“",'"', $content);
        $content = str_replace("”",'"', $content);
        return $content;
    }

    public static function initSymfony()
    {
        $loader = require_once __DIR__.'/../../../../app/bootstrap.php.cache';

        // Load application kernel
        require_once __DIR__.'/../../../../app/AppKernel.php';

        $sfKernel = new AppKernel('dev', true);
        $sfKernel->loadClassCache();
        $sfKernel->boot();
        // Add Symfony container as a global variable to be used in Wordpress
        $sfContainer = $sfKernel->getContainer();

        if (true === $sfContainer->getParameter('kernel.debug', false)) {
//            Debug::enable(E_ALL ^ E_USER_DEPRECATED, false);
        }

        error_reporting(0);


        /** @var \Posse\SurveyBundle\Services\ProjectManager $pm */
        $pm = $sfContainer->get('survos_survey.project_manager');

        $sfRequest = Request::createFromGlobals();
        $sfContainer->get('request_stack')->push($sfRequest);
        $sfContainer->enterScope('request');

        // hack I think, maybe could be achieved different way
        // here we call symfony so it's starting session and handles request correctly
        // this way we can use twig controller rendering etc later
        $sfRequest->server->set('REQUEST_URI', '/public/wp.json');
//        $sfRequest->server->set('REQUEST_URI', '/login');
        $sfResponse = $sfKernel->handle($sfRequest);

        $site = get_blog_details();
        $parts = explode('.', $site->domain);
        $projectCode = reset($parts);
        $project = $pm->getProjectByName($projectCode);

        self::symfony($sfContainer);
        if ($project) {
            $pm->setProject($project);
        }

        // try to authenticate user
        /** @var WP_User $current_user */
        $current_user = null;
        $email = null;
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $email = $current_user->get('user_email');
        }
//        $sfRequest = Request::createFromGlobals();
//        $sfResponse = $sfKernel->handle($sfRequest);

        /** @var \Posse\UserBundle\Propel\User $symfonyUser */
        $symfonyUser = self::getSymfonyUser();

        // if symfony user not logged in or different than our user then reauthenticate by email
        if (is_object($symfonyUser) && (!is_user_logged_in() || $symfonyUser->getEmail() != $email)) {
            //                self::getWpService()->authenticateUserByEmail($email);
            $wpuser = get_user_by('email', $symfonyUser->getEmail());
            if ($wpuser) {
                //authenticate local user if found
                if (self::symfony('security.authorization_checker')->isGranted('PROJECT_OWNER') && !$wpuser->has_cap('administrator')) {
                    $wpuser->add_role('administrator');
                    wp_update_user($wpuser);
                } elseif(!self::symfony('security.authorization_checker')->isGranted('PROJECT_OWNER') && !$wpuser->has_cap('administrator'))  {
                    $wpuser->remove_role('administrator');
                    wp_update_user($wpuser);
                }
                wp_set_auth_cookie($wpuser->id);
                wp_redirect(home_url());
            }
        } else {
            // if logged in but symfony user logged out, then log out locally

            if (!is_object($symfonyUser) && !is_main_site() && is_user_logged_in()) {
                wp_logout();
                wp_redirect(home_url());
            }
        }

        if (!current_user_can('administrator') && !is_admin()) {
            show_admin_bar(false);
        }

//      $sfResponse->send();
//      $sfKernel->terminate($sfRequest, $sfResponse);
    }

    /**
     * Retrieves or sets the Symfony Dependency Injection container
     *
     * @param ContainerInterface|string $id
     *
     * @return mixed
     */
    public static function symfony($id = null, $parameter = false, $def = '')
    {
        static $container;

        if (is_null($id)) {
            return $container;
        }
        if ($id instanceof ContainerInterface) {
            $container = $id;
            return;
        }
        if (!$container) {
            return null;
        }
        if ($parameter !== false) {
            return $container->getParameter($parameter, $def);
        } else {
            return $container->get($id);
        }
    }


    public static function getParameter($param, $def = '')
    {
        return self::symfony(null, $param, $def);
    }

    public static function getBlogFullDomain($slug)
    {
        $dom = self::getParameter('wordpress.master_domain');
        return $slug.".".$dom;
    }

    /**
     * get project manager service
     */
    public static function getProjectManager()
    {
        return self::symfony('survos_survey.project_manager');
    }

    /**
     * @return \Posse\ServiceBundle\Services\WordpressService
     */
    private static function getWpService()
    {
        $svc = self::symfony('posse.wordpress');
        if (!$svc) {
            throw new Exception('Couldn\'t load Posse Wordpress service');
        }
        return $svc;
    }

    /**
     * @return User
     */
    public static function getSymfonyUser()
    {
        $token = self::symfony('security.token_storage')->getToken();
        if ($token) {
            return $token->getUser();
        }
        return null;
    }

    /**
     * get project manager service
     */
    public static function renderTemplate($template, $atts = [])
    {
        return self::symfony('twig')->render($template, $atts);
    }

    /**
     * get project manager service
     */
    public static function getJob($code)
    {
        return self::symfony('survos.service.job')->getJob($code);
    }

    /**
     * get ct object
     */
    public static function getCt($code)
    {
        $ct = self::symfony('survos.clinical_trials')->getCt($code);
        return $ct;
    }

    /**
     * get survey
     */
    public static function getSurvey($code)
    {
        return self::symfony('survos.service.survey')->getSurvey($code);
    }

    public static function posse_custom_query_vars($vars)
    {
        return $vars;
    }

    /**
     * get surveys
     */
    public static function getSurveys()
    {
        /** @var \Posse\SurveyBundle\Model\Project $project */
        $project = self::getProjectManager()->getProject();
        if (!$project) {
            echo "!Project not found!";
        }
        return $project->getSurveys();
    }

    /**
     * get surveys
     */
    public static function getProjectRoles()
    {
        return [
            'this-isnt-working-yet',
            'visitor',
            'participant',
            'field worker',
            'admin',
        ];
    }

    /**
     * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
     * @static
     */
    public static function plugin_activation()
    {
    }

    /**
     * Removes all connection options
     * @static
     */
    public static function plugin_deactivation()
    {
        //tidy up
    }
}

/**
 * Add a hidden field with the theme's value
 */
function posse_theme_hidden_fields()
{ ?>

    <?php
    $project = isset($_GET['project']) ? $_GET['project'] : '';
    ?>
    <input type="hidden" name="project_code" value="<?php echo $project; ?>">
<?php }

add_action('signup_hidden_fields', 'posse_theme_hidden_fields');

function posse_add_signup_meta($result)
{

    return [
        'posse_user_role' => $_POST['posse_user_role'],
        'project_code'    => $_POST['project_code'],
    ];
}

add_filter('add_signup_meta', 'posse_add_signup_meta');

/**
 * @param $user_id
 * @param $password
 * @param $meta
 */
function posse_wpmu_activate_user($user_id, $password, $meta)
{
    if (isset($meta['project_code']) && isset($meta['posse_user_role'])) {
        $project_role = get_user_meta($user_id, 'project_role', true);
        if (!$project_role) {
            $project_role = [];
        }
        $project_role[$meta['project_code']] = $meta['posse_user_role'];
        update_user_meta($user_id, 'project_role', $project_role);

        $blog = get_blog_details(['domain' => Posse::getBlogFullDomain($meta['project_code'])]);

        if ($blog) {
            add_user_to_blog($blog->blog_id, $user_id, 'subscriber');
            ?>
            <script>window.location.replace("<?php echo $blog->siteurl ?>");</script><?php
        }

        unset($meta['project_code']);
        unset($meta['posse_user_role']);
    }
    // update other meta fields
    foreach ($meta as $key => $val) {
        update_user_meta($user_id, $key, $val);
    }

    Posse::syncUser(get_userdata($user_id), $password);
}

add_filter('wpmu_activate_user', 'posse_wpmu_activate_user', 1, 3);