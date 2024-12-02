<?php if (!empty($metadata)): ?>
    <div class="activity-metadata">
        <?php if (!empty($metadata['note'])): ?>
            <p><strong>Note:</strong> <?php echo esc_html($metadata['note']); ?></p>
        <?php endif; ?>
        <?php if (!empty($metadata['lieu'])): ?>
            <p><strong>Lieu:</strong> <?php echo esc_html($metadata['lieu']); ?></p>
        <?php endif; ?>
        <?php if (!empty($metadata['nb_personne'])): ?>
            <p><strong>Nombre de personnes:</strong> <?php echo esc_html($metadata['nb_personne']); ?></p>
        <?php endif; ?>
        <?php if (!empty($metadata['duree'])): ?>
            <p><strong>Durée:</strong> <?php echo esc_html($metadata['duree']); ?></p>
        <?php endif; ?>
        <?php if (!empty($metadata['langue_fr'])): ?>
            <p><strong>Langue (FR):</strong> <?php echo esc_html($metadata['langue_fr']); ?></p>
        <?php endif; ?>
        <?php if (!empty($metadata['langue_en'])): ?>
            <p><strong>Langue (EN):</strong> <?php echo esc_html($metadata['langue_en']); ?></p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <p>No metadata available.</p>
<?php endif; ?>
