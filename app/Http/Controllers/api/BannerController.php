<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Models\Banner;
use App\Models\User;
use App\Models\ColorScheme;
use App\Models\Template;

use ZipArchive;
use Carbon\Carbon;

class BannerController extends Controller {
    /**
     * Save image to server
     * 
     * @param string $banner_uuid Image banners UUID
     * @param string $image_b64 Image in base64 format
     * 
     * @return string|array Path to file or error message
     */
    private function saveImage($banner_uuid, $image_b64) {
        try {
            if(strpos($image_b64, ",") === false) {
                throw new \Exception;
            }
            $image_b64 = substr($image_b64, strpos($image_b64, ",") + 1);

            $file = base64_decode($image_b64);

            $path_for_banner = public_path() . '/banners/' . $banner_uuid;
            $path = $path_for_banner . "/image.jpeg";

            if(!file_exists($path_for_banner)) {
                mkdir($path_for_banner);
            }

            file_put_contents($path, $file);

            return '/banners/' . $banner_uuid . "/image.jpeg";
        } catch(\Exception $e) {
            return ['error' => ['Image is in wrong format']];
        }
    }

    /**
     * Get all banners from user
     * 
     * @return Response JSON object with banners or error
     */
    public function getAll() {
        //Declare return variable as array
        $return_array = [];
        
        //Find user model
        try {
            $user = User::find(Auth::user()->uuid);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Cannot get user'
            ], 500);
        }

        //Get all user banners and order them
        try {
            $banners = $user->banners()->orderBy('updated_at', 'DESC')->get();
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Cannot get banners from user'
            ], 500);
        }

        //Cycle all banners and find what types they have (e.g. Giga / Tower)
        foreach($banners as $banner) {
            $banner_array = $banner->toArray();
            try {
                $banner_types = $banner->template->bannerTypes();
            } catch(\Exception $e) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Cannot get banner types'
                ], 500);
            }

            //Find banner color scheme
            try {
                $color_scheme = $banner->colorScheme;
            } catch(\Exception $e) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Cannot get banner color scheme'
                ], 500);
            }

            //Remove unneeded return values
            unset($banner_array['template_id']);
            unset($banner_array['color_scheme_id']);

            //Add banner and extra info to return array
            array_push($return_array, array_merge(
                $banner_array,
                [
                    'banner_types' => $banner_types,
                    'color_scheme' => $color_scheme
                ]
            ));
        }

        return response()->json([
            'status' => 'success', 
            'banners' => $return_array
        ], 200);
    }

    /**
     * Get user banner information
     * 
     * @param Request $request Request containing banner UUID
     * @return Response JSON object with banner information or error
     */
    public function get(Request $request) {
        $uuid = $request->input('uuid');
        
        //Check if there's a banner UUID input
        if(!empty($uuid)) {
            //Find banner by UUID
            try {
                $banner = Banner::findOrFail($uuid);
                if(Auth::user()->uuid !== $banner->created_by) {
                    return response()->json([
                        'status'  => 'error', 
                        'message' => 'Banner not created by user'
                    ], 422);
                }

                return response()->json([
                    'status' => 'success',
                    'banner' => $banner
                ], 200);

            } catch(\Exception $e) {

                return response()->json([
                    'status'  => 'error', 
                    'message' => 'No banner found with UUID'
                ], 422);
            }
        } else { 
            return response()->json([
                'status'  => 'error', 
                'message' => 'No banner UUID passed'
            ], 422);
        }
    }

    /**
     * Get public banner information
     * 
     * @param Request $request Request containing banner UUID
     * @return Response JSON object with banner information or error
     */
    public function getPublicBanner(Request $request) {
        $uuid = $request->input('uuid');

        //Check if there's a banner UUID input
        if(!empty($uuid)) {
            //Find banner by UUID
            try {
                $banner = Banner::findOrFail($uuid);
            } catch(\Exception $e) {

                return response()->json([
                    'status'  => 'error', 
                    'message' => 'Cannot get banner information'
                ], 500);
            }

            //Find user's username who created this banner
            try {
                $created_user = $banner->user->username;
            } catch(\Exception $e) {
                //Not a fatal error if there is no username
                $created_user = NULL;
            }

            //Find banner types
            try {
                $banner_types = $banner->template->bannerBlocksByTypes();
            } catch(\Exception $e) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Cannot get banner types'
                ], 500);
            }

            //Find color scheme info
            try {
                $banner->colorScheme;
            } catch(\Exception $e) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Cannot get banner color scheme'
                ], 500);
            }

            //Remove unused information
            $banner_modal_array = $banner->toArray();
            unset($banner_modal_array['color_scheme_id']);
            unset($banner_modal_array['template_id']);
            unset($banner_modal_array['template']);
            unset($banner_modal_array['user']);

            //Create array to return
            $banner_array = array_merge(
                $banner_modal_array,
                [ 
                    'created_by_name' => $created_user,
                    'banner_types' => $banner_types,
                ]
            );


            return response()->json([
                'status' => 'success',
                'banner' => $banner_array
            ], 200);
        } else { 
            return response()->json([
                'status'  => 'error', 
                'message' => 'No banner UUID passed'
            ], 422);
        }
    }

    /**
     * Get public banners
     * 
     * @param Request $request Request containing page number and filters
     * @return Response JSON object with banners or error
     */
    public function getAllPublicBanners(Request $request) {
        try {
            $banner_query = Banner::query();
            $page = 1;
            $max_items_per_page = 6;

            //Order
            $order_by = (!empty($request->input('order'))) ? $request->input('order') : 'DESC';
            if($order_by == 'ASC') {
                $banner_query->orderBy('created_at', $order_by);
            } else {
                $banner_query->orderByDesc('created_at', $order_by);
            }

            //Date
            $created_at_oldest = (!empty($request->input('created_at'))) ? $request->input('created_at') : '0';
            if(
                $created_at_oldest == 7 ||
                $created_at_oldest == 31 ||
                $created_at_oldest == 186 ||
                $created_at_oldest == 366 
            ) {
                $max_time = Carbon::now()->subDays($created_at_oldest);
                $banner_query->whereDate('created_at', '>=', $max_time);
            }

            if(!empty($request->input('page'))) {
                $page = $request->input('page');
            }

            $banner_query->paginate($max_items_per_page, ['*'], 'page', $page);

            $banners = $banner_query->get();            
            $banners_array = [];
            foreach($banners as $banner) {
                //Find banner types
                try {
                    $banner_types = $banner->template->bannerBlocksByTypes();
                } catch(\Exception $e) {
                    return response()->json([
                        'status' => 'error', 
                        'message' => 'Cannot get banner types'
                    ], 500);
                }

                //Find color scheme info
                try {
                    $banner->colorScheme;
                } catch(\Exception $e) {
                    return response()->json([
                        'status' => 'error', 
                        'message' => 'Cannot get banner color scheme'
                    ], 500);
                }

                //Remove unused information
                $banner_modal_array = $banner->toArray();
                unset($banner_modal_array['color_scheme_id']);
                unset($banner_modal_array['template_id']);
                unset($banner_modal_array['template']);

                //Create array to return
                $banner_array = array_merge(
                    $banner_modal_array,
                    [
                        'banner_types' => $banner_types,
                    ]
                );
                array_push($banners_array, $banner_array);
            }

            $has_next_page = (Banner::all()->count() / $max_items_per_page) > $page;

            return response()->json([
                'status'  => 'success', 
                'banners' => $banners_array,
                'has_next_page' => $has_next_page
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error', 
                'message' => 'Cannot retrieve all banners'
            ], 500);
        }
    }

    /**
     * Store banner information to database
     * 
     * @param Request $request Request containing all info about saveable banner
     * 
     * @return Response JSON response with success or error
     */
    public function store(Request $request) {
        //Get user UUID
        try {
            $user_uuid = Auth::user()->uuid;
        } catch(\Exception $e) {
            return response()->json([
                'status'    => 'error', 
                'messages'  => 'No user information'
            ], 403);
        }

        //Validate that inputs are fine
        $validator = Validator::make($request->all(), [
            'color_scheme_id'   => 'required|gt:0',
            'template_id'       => 'required|gt:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 'error', 
                'messages'  => $validator->messages()
            ], 422);
        }

        //Check if color scheme can be used by user
        $color_scheme_id = $request->input('color_scheme_id');
        try {
            $color_scheme = ColorScheme::findOrFail($color_scheme_id);

            //Check if color scheme is default or was created by user
            if(!($color_scheme->user_uuid === null || $color_scheme->user_uuid == $user_uuid)) {
                return response()->json([
                    'status'    => 'error', 
                    'messages'  => ['color_scheme_id' => ['Color scheme doesn`t belong to user']]
                ], 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status'    => 'error', 
                'messages'  => ['color_scheme_id' => ['No color scheme with this ID']]
            ], 422);
        }

        //Check if template can be used by user
        $template_id = $request->input('template_id');
        try {
            $template = Template::findOrFail($template_id);
            
            //Check if template is default or was created by user
            if(!($template->user_uuid === null || $template->user_uuid == $user_uuid)) {
                return response()->json([
                    'status'    => 'error', 
                    'messages'  => ['template_id' => ['Template doesn`t belong to user']]
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'    => 'error', 
                'messages'  => ['template_id' => ['No template with this ID']]
            ], 422);
        }

        //Validate requested inputs
        $validation_array = [
            'name'      => 'required|min:5|max:255',
            'link_url'  => 'required|regex:/https:\/\/.*/|min:9|max:255'
        ];

        //Get template used blocks and add them to validation
        $template_blocks = array_keys($template->usedBlocks());
        foreach($template_blocks as $block) {
            if($block !== 'image') {
                $validation_array[$block] = 'required|max:255';
            } else {
                $validation_array[$block] = 'required';
            }
        }
        
        //Validate again
        $validator = Validator::make($request->all(), $validation_array);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 'error', 
                'messages'  => $validator->messages()
            ], 422);
        }

        //Create new banner
        if(empty($request->input('uuid'))){
            try {
                //Add UUID and Created by to given inputs
                $uuid = Str::uuid()->toString();
                $extra_variables = [
                    'uuid' => $uuid,
                    'created_by' => $user_uuid
                ];

                //Save image
                if(!empty($request->input('image'))) {
                    $image = $this->saveImage($uuid, $request->input('image'));
                    if(isset($image['error'])) {
                        return response()->json([
                            'status'    => 'error', 
                            'messages'  => ['image' => $image['error']]
                        ], 422);
                    }
                    $extra_variables['image'] = $image;
                }

                //Merge needed variables and extra variables to one array
                $banner_variables = array_merge(
                    $extra_variables, 
                    $request->only('name', 'link_url', 'main_text', 'sub_text', 'call_to_action', 'color_scheme_id', 'template_id')
                );

                //Add banner to database
                $banner = Banner::create($banner_variables);
                return response()->json([
                    'status'    => 'success', 
                    'message'   => 'Banner created successfully',
                    'uuid'      => $banner->uuid
                ], 200);
            } catch(\Exception $e) {
                return response()->json([
                    'status'  => 'error', 
                    'message' => 'Cannot create banner'
                ], 500);
            }
        } else {
        //Edit existing banner

            //Find banner model
            try {
                $banner = Banner::findOrFail($request->input('uuid'));
            } catch(\Exception $e) {
                return response()->json([
                    'status'  => 'error', 
                    'message' => 'Cannot find banner by UUID'
                ], 500);
            }

            //Check if banner created by this user
            if($banner->user->uuid != $user_uuid) {
                return response()->json([
                    'status'  => 'error', 
                    'message' => 'Banner not created by user'
                ], 422);
            }

            //Edit all input fields
            $banner->name = $request->input('name');
            $banner->link_url = $request->input('link_url');
            $banner->main_text = $request->input('main_text');
            $banner->sub_text = $request->input('sub_text');
            $banner->call_to_action = $request->input('call_to_action');
            $banner->color_scheme_id = $color_scheme_id;
            $banner->template_id = $template_id;

            if($request->input('image') && strpos($request->input('image'), "\banners\\") === false){
                //Save image to server
                $image = $this->saveImage($banner->uuid, $request->input('image'));
                if(isset($image['error'])) {
                    return response()->json([
                        'status'    => 'error', 
                        'messages'  => ['image' => $image['error']]
                    ], 422);
                }

                $banner->image = $image;
            }

            try {
                //Save to database
                $banner->save();
                return response()->json([
                    'status'    => 'success', 
                    'message'   => 'Banner edited successfully',
                    'uuid'      => $banner->uuid
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => 'error', 
                    'message' => 'Cannot edit banner'
                ], 500);
            }
        }
    }

    /**
     * Delete banner from database
     * 
     * @param Request $request Request containing banner UUID
     * 
     * @return Response JSON response with success or error
     */
    public function delete(Request $request) {
        //Check if there is a user
        if(empty(Auth::user()) || empty(Auth::user()->uuid)) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Cannot get user'
            ], 500);
        } else {
            $user_uuid = Auth::user()->uuid;
        }

        $uuid = $request->input('uuid');
        
        //Check if input has banner UUID
        if(!empty($uuid)) {
            try {
                //Find banner modal
                $banner = Banner::findOrFail($uuid);

                //Check if same user created
                if($banner->user->uuid != $user_uuid) {
                    return response()->json([
                        'status'  => 'error', 
                        'message' => "Banner not created by user"
                    ], 422);
                }

                //Delete from database
                $banner->delete();

                return response()->json([
                    'status'  => 'success', 
                    'message' => "Banner deleted successfully"
                ], 200);

            } catch(\Exception $e) {

                return response()->json([
                    'status'  => 'error', 
                    'message' => 'No banner with this UUID'
                ], 422);
            }
        } else { 
            return response()->json([
                'status'  => 'error', 
                'message' => 'No banner UUID passed'
            ], 422);
        }
    }

    /**
     * Delete directory and all of its children
     * 
     * @param string $dir Directory path
     */
    private function removeDirectoryRecursively($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "\\" . $object) == "dir") {
                        $this->removeDirectoryRecursively($dir . "\\" . $object);
                    } else {
                        unlink($dir . "\\" . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    /**
     * Export banner to ZIP
     * 
     * @param Request $request Request containing banner UUID
     * 
     * @return Response JSON response with error or ZIP download
     */
    public function export(Request $request) {
        //Test if UUID passed
        $uuid = $request->input('uuid');
        if(empty($uuid)) {
            return response()->json([
                'status'  => 'error', 
                'message' => 'No banner UUID passed'
            ], 422);
        }

        //Find all banner info by UUID
        try {
            $banner = Banner::findOrFail($uuid);
            $template = $banner->template;
            $blocks = $template->bannerBlocksByTypes();

            $color_scheme = $banner->colorScheme;
        } catch(\Exception $e) {
            return response()->json([
                'status'  => 'error', 
                'message' => 'Cannot get banner information'
            ], 500);
        }

        //Set export variables
        $default_styles = '';
        $default_html = '';
        $banner_type_styles = [];
        $banner_type_html = [];
        $banner_type_sizes = [];

        //Set up default styling and HTML
        $default_styles .= 
            'body{' .
                'background-color:' . $color_scheme->background_color . ';' .
            '}' . 
            '.call_to_action{' . 
                'border-radius: 5px;' .
                'background-color:' . $color_scheme->cta_color . ';' .
                'overflow: hidden;' .
            '}' . 
            '.call_to_action span{' . 
                'position: absolute;' . 
                'text-align: center;' . 
                'width: 100%;' . 
                'display: block;' . 
                'top: 50%;' . 
                'transform: translateY(-50%);' . 
                'font-size: 16px;' . 
                'color:' . $color_scheme->text_color . ';' . 
            '}' . 
            '.main_text{' . 
                'font-size: 32px;' . 
                'color:' . $color_scheme->text_color . ';' . 
                'overflow: hidden;' .
            '}' . 
            '.sub_text{' . 
                'color:' . $color_scheme->text_color . ';' . 
                'font-size: 28px;' .
                'overflow: hidden;' .
            '}'; 
        $default_html .= 
            '<!DOCTYPE html>'. "\n" .
            '<head>' . "\n" .
            "\t" . '<title>Adform Toolkit Ad</title>'. "\n" .
            "\t" . '<meta charset="UTF-8">' . "\n" .
            "\t" . '<script type="text/javascript">'. "\n" .
            "\t\t" .' document.write(\'<script src="\'+ (window.API_URL || \'https://s1.adform.net/banners/scripts/rmb/Adform.DHTML.js?bv=\'+ Math.random()) +\'"><\/script>\');' . "\n" .
            "\t" . '</script>' . "\n" .
            "\t" . '<link rel="stylesheet" href="assets/style.css">' . "\n" .
            '</head>' . "\n" .
            '<body>' . "\n";
        $default_html_close = '</body></html>';
        
        //Go through all block positions and add styling and blocks to HTML
        foreach($blocks as $type => $information) {

            //Set body width and height accordting to banner type
            $style = 
                'body{' .
                    'position: relative;' . 
                    'width: ' . $information['sizes']['width'] . 'px;'.
                    'height: ' . $information['sizes']['height'] . 'px;' .
                '}';
            $html = '';

            //Add all active blocks to current banner type HTML
            foreach($information['blocks'] as $block) {
                //Read block title and HTML
                $block_title = $block['block_type']['title'];
                $block_html = $block['block_type']['html'];

                //Replace block HTML placeholder with actual text / image
                if($block_title != "image") {
                    $block_reformatted = str_replace(
                        '{{' . $block_title . '}}', 
                        $banner->{$block_title}, 
                        $block_html
                    );
                } else {
                    $block_reformatted = str_replace(
                        '{{image}}',
                        'assets/image.jpeg',
                        $block_html
                    );
                    $style .= 
                        '.' . $block_title . ' img{' .
                            (($block['width'] > $block['height']) ? 'width: 100%;' : 'height: 100%;') .
                        '}';
                }

                //Add created HTML and CSS to current banner type information
                $html .= $block_reformatted . "\n";
                $style .= 
                    '.' . $block_title . '{' . 
                        'position: absolute;' .
                        'line-height: 1;' . 
                        'top:' . $block['top_offset'] . 'px;' .
                        'left:' . $block['left_offset'] . 'px;' .
                        'width:' . $block['width'] . 'px;' .
                        'height:' . $block['height'] . 'px;' .
                        'z-index:' . $block['z_index'] . 
                    '}';
            }

            //Add banner type HTML and CSS to main array
            $banner_type_html[$type] = $default_html . $html . $default_html_close;
            $banner_type_styles[$type] = $default_styles . $style;
            $banner_type_sizes[$type] = $information['sizes'];
        }

        //Create UUID needed for adform
        $adform_uuid = str_replace('-', '', $uuid);

        //Create path variables for all needed files
        $export_path = public_path() . '\banners\\' . $banner->name . '-export';
        $adfm_properties = public_path() . '\banners\default\ADFBannerProperties.xml';
        $adfm_manifest = public_path() . '\banners\default\manifest.json';
        
        //Create temporary export folder
        if(file_exists($export_path)) {
            $this->removeDirectoryRecursively($export_path);
        }
        mkdir($export_path);

        //Create all neccessary files for download
        foreach($banner_type_sizes as $type => $sizes) {
            //Get adfm properites and change info according to banner type
            $properties = file_get_contents($adfm_properties);
            $properties = str_replace('{{uuid}}', $adform_uuid, $properties);
            $properties = str_replace('{{title}}', $banner->name, $properties);
            $properties = str_replace('{{link}}', $banner->link_url, $properties);

            //Get adfm manifest and change info according to banner type
            $manifest = file_get_contents($adfm_manifest);
            $manifest = str_replace('{{uuid}}', $adform_uuid, $manifest);
            $manifest = str_replace('{{title}}', $banner->name, $manifest);
            $manifest = str_replace('{{width}}', $sizes['width'], $manifest);
            $manifest = str_replace('{{height}}', $sizes['height'], $manifest);
            $manifest = str_replace('{{link}}', $banner->link_url, $manifest);

            //Create file structure for current banner type
            mkdir($export_path . '\\' . $type);
            file_put_contents($export_path . '\\' . $type . '\ADFBannerProperties.xml', $properties);
            mkdir($export_path . '\\' . $type . '\main');
            file_put_contents($export_path . '\\' . $type . '\main\manifest.json', $manifest);
            file_put_contents($export_path . '\\' . $type . '\main\index.html', $banner_type_html[$type]);
            mkdir($export_path . '\\' . $type . '\main\assets');
            file_put_contents($export_path . '\\' . $type . '\main\assets\style.css', $banner_type_styles[$type]);

            //Create zip from banner type
            $zip = new ZipArchive();
            $zip->open($export_path . '\\' . $type . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

            //Add all files to zip
            $zip->addFile($export_path . '\\' . $type . '\ADFBannerProperties.xml', 'ADFBannerProperties.xml');
            $zip->addFile($export_path . '\\' . $type . '\main\manifest.json', 'main/manifest.json');
            $zip->addFile($export_path . '\\' . $type . '\main\index.html', 'main/index.html');
            $zip->addFile($export_path . '\\' . $type . '\main\assets\style.css', 'main/assets/style.css');

            //Add image if banner has image
            if(!empty($banner->image)) {
                copy(
                    public_path() . $banner->image, 
                    $export_path . '\\' . $type . '\main\assets\image.jpeg'
                );
                $zip->addFile($export_path . '\\' . $type . '\main\assets\image.jpeg', 'main/assets/image.jpeg');
            }

            //Save zip
            try {
                $zip->close();
                $this->removeDirectoryRecursively($export_path . '\\' . $type);
            } catch(\Exception $e) {
                return response()->json([
                    'status'  => 'error', 
                    'message' => 'Cannot zip banner'
                ], 500);
            }
        }

        //Zip all banner types into one
        $zip = new ZipArchive();
        $export_file = $export_path . '\\' . $banner->name . '.zip';
        $zip->open($export_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach($banner_type_sizes as $type => $sizes) {
            $zip->addFile($export_path . '\\' . $type . '.zip', $type . '.zip');
        }
        //Save zip
        try {
            $zip->close();
        } catch(\Exception $e) {
            return response()->json([
                'status'  => 'error', 
                'message' => 'Cannot zip banner'
            ], 500);
        }

        //Delete unused zips
        foreach($banner_type_sizes as $type => $sizes) {
            try {
                unlink($export_path . '\\' . $type . '.zip');
            } catch (\Exception $e) {
                //Failed to delete, but no user response needed
            }
        }

        return response()->download($export_file, basename($export_file))->deleteFileAfterSend(true);
    }
}
