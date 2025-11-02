<div class="pfob-project-nav">
    <?php if ( isset( $pfob_project ) && $pfob_project ) : ?>
        <div class="pfob-project-header">
            <h2><?php echo esc_html( $pfob_project->name ); ?></h2>
            <?php if ( $pfob_project->description ) : ?>
                <p class="pfob-project-description"><?php echo esc_html( $pfob_project->description ); ?></p>
            <?php endif; ?>
        </div>

        <nav class="pfob-project-tools">
            <?php
            $tools = PFOB_Project::get_tools( $pfob_project->id );
            foreach ( $tools as $tool ) :
                if ( ! $tool->is_enabled ) continue;
                $tool_url = PFOB_Template::get_tool_url( $pfob_project, $tool->tool_type );
                $active = ( strpos( $_SERVER['REQUEST_URI'], '/' . $tool->tool_type ) !== false ) ? 'active' : '';
            ?>
                <a href="<?php echo esc_url( $tool_url ); ?>" class="pfob-tool-item <?php echo $active; ?>">
                    <span class="pfob-tool-icon"><?php echo PFOB_Template::project_icon( $tool->tool_type ); ?></span>
                    <span class="pfob-tool-name"><?php echo esc_html( ucfirst( $tool->tool_type ) ); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="pfob-project-members">
            <h4>Team</h4>
            <?php
            $members = PFOB_Project::get_members( $pfob_project->id );
            foreach ( $members as $member ) :
            ?>
                <div class="pfob-member-item">
                    <?php echo PFOB_Template::user_avatar( $member->ID, 32 ); ?>
                    <span><?php echo esc_html( $member->display_name ); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
