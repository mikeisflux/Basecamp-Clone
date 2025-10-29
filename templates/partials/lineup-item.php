<?php
/**
 * Lineup Item Partial
 * Renders a single item in the lineup timeline
 *
 * @var array $item The lineup item data
 */
?>
<div class="pfob-lineup-item"
     data-type="<?php echo esc_attr( $item['type'] ); ?>"
     data-project-id="<?php echo esc_attr( $item['project_id'] ); ?>">

    <div class="pfob-lineup-item-date">
        <div class="pfob-lineup-date-day"><?php echo date( 'j', strtotime( $item['date'] ) ); ?></div>
        <div class="pfob-lineup-date-month"><?php echo date( 'M', strtotime( $item['date'] ) ); ?></div>
    </div>

    <div class="pfob-lineup-item-content">
        <div class="pfob-lineup-item-header">
            <span class="pfob-lineup-item-type">
                <?php if ( $item['type'] === 'todo' ) : ?>
                    ✅ To-do
                <?php elseif ( $item['type'] === 'event' ) : ?>
                    📅 Event
                    <?php if ( ! empty( $item['time'] ) ) : ?>
                        <span class="pfob-lineup-time"><?php echo esc_html( $item['time'] ); ?></span>
                    <?php endif; ?>
                <?php endif; ?>
            </span>
        </div>

        <h3 class="pfob-lineup-item-title">
            <a href="<?php echo esc_url( $item['url'] ); ?>">
                <?php echo esc_html( $item['title'] ); ?>
            </a>
        </h3>

        <div class="pfob-lineup-item-meta">
            <span class="pfob-lineup-project">
                <?php echo esc_html( $item['project'] ); ?>
            </span>

            <?php if ( ! empty( $item['location'] ) ) : ?>
                <span class="pfob-lineup-location">
                    📍 <?php echo esc_html( $item['location'] ); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>
