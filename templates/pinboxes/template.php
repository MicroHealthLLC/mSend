<?php
/*
Template name:
PinBoxes

Inspired by the awesome desing of Pinterest!
*/
$ld = 'pinboxes_template'; // specify the language domain for this template

if ( !empty( $_GET['category'] ) ) {
	$category_filter = $_GET['category'];
}

include_once(ROOT_DIR.'/templates/common.php'); // include the required functions for every template

$window_title = __('Available files','pinboxes_template');

$count = count($my_files);
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php echo html_output( $client_info['name'].' | '.$window_title . ' &raquo; ' . SYSTEM_NAME ); ?></title>
		<link rel="stylesheet" media="all" type="text/css" href="<?php echo $this_template; ?>main.css" />
		<link rel="shortcut icon" href="<?php echo BASE_URI; ?>favicon.ico" />
		<link href='<?php echo PROTOCOL; ?>://fonts.googleapis.com/css?family=Metrophobic' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" href="<?php echo $this_template; ?>/font-awesome-4.6.3/css/font-awesome.min.css">
		
		<script src="<?php echo PROTOCOL; ?>://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js" type="text/javascript"></script>
		<script type="text/javascript" src="<?php echo $this_template; ?>/js/jquery.masonry.min.js"></script>
		<script type="text/javascript" src="<?php echo $this_template; ?>/js/imagesloaded.pkgd.min.js"></script>
		
		<script type="text/javascript">
			$(document).ready(function()
				{
					var $container = $('.photo_list');
					$container.imagesLoaded(function(){
						$container.masonry({
							itemSelector	: '.photo',
							columnWidth		: '.photo'
						});
					});

					$('.button').click(function() {
						$(this).blur();
					});
					
					$('.categories_trigger a').click(function(e) {
						if ( $('.categories').hasClass('visible') ) {
							close_menu();
						}
						else {
							open_menu();
						}
					});
					
					$('.content_cover').click(function(e) {
						close_menu();
					});
					
					function open_menu() {
						$('.categories').addClass('visible');
						$('.categories').stop().slideDown();
						$('.content_cover').stop().fadeIn(200);
					}

					function close_menu() {
						$('.categories').removeClass('visible');
						$('.content_cover').stop().fadeOut(200);
						$('.categories').stop().slideUp();
					}
				}
			);
		</script>
	</head>
	
	<body>
		<div id="header">
			<?php if ($logo_file_info['exists'] === true) { ?>
				<div id="branding">
					<img src="<?php echo TIMTHUMB_URL; ?>?src=<?php echo $logo_file_info['url']; ?>&amp;w=300" alt="<?php echo THIS_INSTALL_SET_TITLE; ?>" />
				</div>
			<?php } ?>
		</div>

		<div id="menu">
			<p class="welcome">
				<?php _e('Welcome','pinboxes_template'); ?>, <?php echo html_output($client_info['name']); ?>
			</p>
			<ul>
				<li id="search_box">
					<form action="" name="files_search" method="post">
						<input type="text" name="search" id="search_text" value="<?php echo (isset($_POST['search']) && !empty($_POST['search'])) ? html_output($_POST['search']) : ''; ?>" placeholder="<?php _e('Search...','pinboxes_template'); ?>">
						<button type="submit" id="search_go"><i class="fa fa-search" aria-hidden="true"></i></button>
					</form>
				</li>
				<?php
					if ( !empty( $get_categories['categories'] ) ) {
						$url_client_id	= ( !empty($_GET['client'] ) && CURRENT_USER_LEVEL != '0') ? $_GET['client'] : null;
						$link_template	= BASE_URI . 'my_files/';
				?>
						<li class="categories_trigger">
							<a href="#" target="_self"><i class="fa fa-filter" aria-hidden="true"></i> <?php _e('Categories', 'pinboxes_template'); ?></a>
							<ul class="categories">
								<li class="filter_all_files"><a href="<?php echo BASE_URI . 'my_files/'; if ( !empty( $url_client_id ) ) { echo '?client=' . $url_client_id; }; ?>"><?php  _e('All files', 'pinboxes_template'); ?></a></li>
								<?php
									foreach ( $get_categories['categories'] as $category ) {
										$link_data	= array(
																'client'	=> $url_client_id,
																'category'	=> $category['id'],
															);
										$link_query	= http_build_query($link_data);
								?>
										<li><a href="<?php echo $link_template . '?' . $link_query; ?>"><?php echo $category['name']; ?></a></li>
								<?php
									}
								?>							
							</ul>
						</li>
				<?php
					}
				?>
				<li>
					<a href="<?php echo BASE_URI; ?>upload-from-computer.php" target="_self"><i class="fa fa-cloud-upload" aria-hidden="true"></i> <?php _e('Upload files', 'pinboxes_template'); ?></a>
				</li>
				<li>
					<a href="<?php echo BASE_URI; ?>process.php?do=logout" target="_self"><i class="fa fa-sign-out" aria-hidden="true"></i> <?php _e('Logout', 'pinboxes_template'); ?></a>
				</li>
			</ul>
		</div>
			
		<div id="content">
			<div class="content_cover"></div>
			<div class="wrapper">
		
		<?php
			if (!$count) {
		?>
				<div class="no_files">
					<?php
						_e('There are no files.','pinboxes_template');
					?>
				</div>
		<?php
			}
			else {
		?>
				<div class="photo_list">
				<?php
					foreach ($my_files as $file) {
						$download_link = make_download_link($file);
						$date = date(TIMEFORMAT_USE,strtotime($file['timestamp']));
				?>
						<div class="photo <?php if ($file['expired'] == true) { echo 'expired'; } ?>">
							<div class="photo_int">
								<?php
									/**
									 * Generate the thumbnail if the file is an image.
									 */
									$pathinfo = pathinfo($file['url']);
									$extension = strtolower($pathinfo['extension']);
									$img_formats = array('gif','jpg','pjpeg','jpeg','png');
									if (in_array($extension,$img_formats)) {
								?>
										<div class="img_prev">
											<?php
												if ($file['expired'] == false) {
											?>
													<a href="<?php echo $download_link; ?>" target="_blank">
														<?php
															$this_thumbnail_url = UPLOADED_FILES_URL.$file['url'];
															if (THUMBS_USE_ABSOLUTE == '1') {
																$this_thumbnail_url = BASE_URI.$this_thumbnail_url;
															}
														?>
														<img src="<?php echo TIMTHUMB_URL; ?>?src=<?php echo $this_thumbnail_url; ?>&amp;w=250&amp;q=<?php echo THUMBS_QUALITY; ?>" alt="<?php echo htmlentities($file['name']); ?>" />
													</a>
											<?php
												}
											?>
										</div>
								<?php
									} else {
										if ($file['expired'] == false) {
								?>
											<div class="ext_prev">
												<a href="<?php echo $download_link; ?>" target="_blank">
													<h6><?php echo $extension; ?></h6>
												</a>
											</div>
								<?php
										}
									}
								?>
							</div>
							<div class="img_data">
								<h2><?php echo htmlentities($file['name']); ?></h2>
								<div class="photo_info">
									<?php echo $file['description']; ?>
									<p class="file_size">
										<?php
											$file_absolute_path = UPLOADED_FILES_FOLDER . $file['url'];
											if ( file_exists( $file_absolute_path ) ) {
												$this_file_size = format_file_size(get_real_size(UPLOADED_FILES_FOLDER.$file['url']));
												_e('File size:','pinboxes_template'); ?> <strong><?php echo $this_file_size; ?></strong>
										<?php
											}
										?>
									</p>

									<p class="exp_date">
										<?php
											if ( $file['expires'] == '1' ) {
												$exp_date = date( TIMEFORMAT_USE, strtotime( $file['expiry_date'] ) );
												_e('Expiration date:','pinboxes_template'); ?> <span><?php echo $exp_date; ?></span>
										<?php
											}
										?>
									</p>
								</div>
								<div class="download_link">
									<?php
										if ($file['expired'] == false) {
									?>
											<a href="<?php echo $download_link; ?>" target="_blank" class="button button_gray">
												<?php _e('Download','pinboxes_template'); ?>
											</a>
									<?php
										}
										else {
									?>
											<?php _e('File expired','pinboxes_template'); ?>
									<?php
										}
									?>
								</div>
							</div>
						</div>
					<?php
						}
					?>
				</div>
			<?php
			}
			?>
		
			</div>
	
			<?php default_footer_info(); ?>
	
		</div>
	
	</body>
</html>