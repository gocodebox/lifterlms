<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$meta_key = '_achievement_earned';

$user = new LLMS_Person;
$achievements = $user->get_user_postmetas_by_key( get_current_user_id(), $meta_key );

?>

<div class="llms-my-achievements">
	<?php echo  '<h3>' .__( 'My Achievements', 'lifterlms' ) . '</h3>'; 
	if ($achievements) { 
		
		?>
	<ul class="listing-achievements">
	<?php foreach ( $achievements as $key => $value ) : 

			$meta = get_post_meta($value->meta_value);

			$achievement_title = $meta['_llms_achievement_title'][0];
			$achievement_content = $meta['_llms_achievement_content'][0];

			$achievementimage_id = $meta['_llms_achievement_image'][0]; // Get Image Meta
			$achievementimage = wp_get_attachment_image_src($achievementimage_id, 'achievement'); //Get Right Size Image for Print Template

			if ($achievementimage == '') {
				$achievementimage = apply_filters( 'lifterlms_placeholder_img_src', LLMS()->plugin_url() . '/assets/images/optional_achievement.png' );
			}
			$achievementimage_width = 120;
			$achievementimage_height = 120;



	?>
	
		<li class="achievement-item">
			<div>
				<img src="<?php echo $achievementimage; ?>" width="<?php echo $achievementimage_width ?>" height="<?php echo $achievementimage_height ?>"/>
			</div>
			<div>
				<h4><?php echo $achievement_title; ?></h4>
			</div>
			<div>
				<p><?php echo $achievement_content; ?></p>
			</div>
			<div>
				<p><?php echo date('M d, Y', strtotime($value->updated_date)); ?></p>
			</div>
				
			<div>
				
			</div>

		</li>

	<?php endforeach; ?>

	</ul>
	<?php 
	}
	else {
		echo  '<p>' .__( 'Complete courses and lessons to earn achievements.', 'lifterlms' ) . '</p>'; 
	}
	?>
</div>




