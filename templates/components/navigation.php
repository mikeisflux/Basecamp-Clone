<div class="bcwp-project-nav">
    <?php if ( isset( $bcwp_project ) && $bcwp_project ) : ?>
        <div class="bcwp-project-header">
            <h2><?php echo esc_html( $bcwp_project->name ); ?></h2>
            <?php if ( $bcwp_project->description ) : ?>
                <p class="bcwp-project-description"><?php echo esc_html( $bcwp_project->description ); ?></p>
            <?php endif; ?>
        </div>

        <nav class="bcwp-project-tools">
            <?php
            $tools = BCWP_Project::get_tools( $bcwp_project->id );
            foreach ( $tools as $tool ) :
                if ( ! $tool->is_enabled ) continue;
                $tool_url = BCWP_Template::get_tool_url( $bcwp_project, $tool->tool_type );
                $active = ( strpos( $_SERVER['REQUEST_URI'], '/' . $tool->tool_type ) !== false ) ? 'active' : '';
            ?>
                <a href="<?php echo esc_url( $tool_url ); ?>" class="bcwp-tool-item <?php echo $active; ?>">
                    <span class="bcwp-tool-icon"><?php echo BCWP_Template::project_icon( $tool->tool_type ); ?></span>
                    <span class="bcwp-tool-name"><?php echo esc_html( ucfirst( $tool->tool_type ) ); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="bcwp-project-members">
            <h4>Team</h4>
            <?php
            $members = BCWP_Project::get_members( $bcwp_project->id );
            foreach ( $members as $member ) :
            ?>
                <div class="bcwp-member-item">
                    <?php echo BCWP_Template::user_avatar( $member->ID, 32 ); ?>
                    <span><?php echo esc_html( $member->display_name ); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
