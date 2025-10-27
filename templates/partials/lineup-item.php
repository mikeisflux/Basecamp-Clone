<?php
/**
 * Lineup Item Partial
 * Renders a single item in the lineup timeline
 *
 * @var array $item The lineup item data
 */
?>
<div class="bcwp-lineup-item"
     data-type="<?php echo esc_attr( $item['type'] ); ?>"
     data-project-id="<?php echo esc_attr( $item['project_id'] ); ?>">

    <div class="bcwp-lineup-item-date">
        <div class="bcwp-lineup-date-day"><?php echo date( 'j', strtotime( $item['date'] ) ); ?></div>
        <div class="bcwp-lineup-date-month"><?php echo date( 'M', strtotime( $item['date'] ) ); ?></div>
    </div>

    <div class="bcwp-lineup-item-content">
        <div class="bcwp-lineup-item-header">
            <span class="bcwp-lineup-item-type">
                <?php if ( $item['type'] === 'todo' ) : ?>
                    ✅ To-do
                <?php elseif ( $item['type'] === 'event' ) : ?>
                    📅 Event
                    <?php if ( ! empty( $item['time'] ) ) : ?>
                        <span class="bcwp-lineup-time"><?php echo esc_html( $item['time'] ); ?></span>
                    <?php endif; ?>
                <?php endif; ?>
            </span>
        </div>

        <h3 class="bcwp-lineup-item-title">
            <a href="<?php echo esc_url( $item['url'] ); ?>">
                <?php echo esc_html( $item['title'] ); ?>
            </a>
        </h3>

        <div class="bcwp-lineup-item-meta">
            <span class="bcwp-lineup-project">
                <?php echo esc_html( $item['project'] ); ?>
            </span>

            <?php if ( ! empty( $item['location'] ) ) : ?>
                <span class="bcwp-lineup-location">
                    📍 <?php echo esc_html( $item['location'] ); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>
