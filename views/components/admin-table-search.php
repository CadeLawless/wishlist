<?php
/**
 * Reusable admin table search input component
 * 
 * @param array $options Configuration options
 * @param string $options['placeholder'] Placeholder text (default: "Search...")
 * @param string $options['input_id'] Input ID (default: "admin-table-search")
 * @param string $options['input_class'] Additional CSS classes for input
 * @param string $options['container_class'] Additional CSS classes for container
 */

$placeholder = $options['placeholder'] ?? 'Search...';
$inputId = $options['input_id'] ?? 'admin-table-search';
$inputClass = $options['input_class'] ?? '';
$containerClass = $options['container_class'] ?? '';
$searchTerm = $options['search_term'] ?? '';
?>

<div class="admin-table-search-container <?php echo htmlspecialchars($containerClass); ?>">
    <div style="position: relative; max-width: 400px;">
        <input 
            type="text" 
            id="<?php echo htmlspecialchars($inputId); ?>" 
            class="admin-table-search-input <?php echo htmlspecialchars($inputClass); ?>" 
            placeholder="<?php echo htmlspecialchars($placeholder); ?>"
            value="<?php echo htmlspecialchars($searchTerm); ?>"
        />
        <button 
            type="button" 
            class="clear-search" 
            style="display: none;"
            title="Clear search"
        >
            ×
        </button>
    </div>
</div>

