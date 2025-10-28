<?php
/**
 * Reusable sort/filter form component
 * 
 * @param array $options Configuration options
 * @param string $options['form_action'] Form action URL
 * @param string $options['form_method'] Form method (default: POST)
 * @param string $options['form_class'] Additional CSS classes for form
 * @param string $options['sort_priority'] Current sort priority value
 * @param string $options['sort_price'] Current sort price value
 * @param string $options['priority_id'] ID for priority select (default: sort-priority)
 * @param string $options['price_id'] ID for price select (default: sort-price)
 * @param string $options['priority_name'] Name for priority select (default: sort_priority)
 * @param string $options['price_name'] Name for price select (default: sort_price)
 * @param string $options['data_attributes'] Additional data attributes for selects
 */

// Set defaults
$form_action = $options['form_action'] ?? '';
$form_method = $options['form_method'] ?? 'POST';
$form_class = $options['form_class'] ?? 'filter-form center';
$sort_priority = $options['sort_priority'] ?? '';
$sort_price = $options['sort_price'] ?? '';
$priority_id = $options['priority_id'] ?? 'sort-priority';
$price_id = $options['price_id'] ?? 'sort-price';
$priority_name = $options['priority_name'] ?? 'sort_priority';
$price_name = $options['price_name'] ?? 'sort_price';
$data_attributes = $options['data_attributes'] ?? '';
?>

<form class="<?php echo htmlspecialchars($form_class); ?>" method="<?php echo htmlspecialchars($form_method); ?>" action="<?php echo htmlspecialchars($form_action); ?>">
    <div class="filter-inputs">
        <div class="filter-input">
            <label for="<?php echo htmlspecialchars($priority_id); ?>">Sort by Priority</label><br>
            <select class="select-filter" id="<?php echo htmlspecialchars($priority_id); ?>" name="<?php echo htmlspecialchars($priority_name); ?>" <?php echo $data_attributes; ?>>
                <option value="">None</option>
                <option value="1" <?php echo $sort_priority == "1" ? 'selected' : ''; ?>>Highest to Lowest</option>
                <option value="2" <?php echo $sort_priority == "2" ? 'selected' : ''; ?>>Lowest to Highest</option>
            </select>
        </div>
        <div class="filter-input">
            <label for="<?php echo htmlspecialchars($price_id); ?>">Sort by Price</label><br>
            <select class="select-filter" id="<?php echo htmlspecialchars($price_id); ?>" name="<?php echo htmlspecialchars($price_name); ?>" <?php echo $data_attributes; ?>>
                <option value="">None</option>
                <option value="1" <?php echo $sort_price == "1" ? 'selected' : ''; ?>>Lowest to Highest</option>
                <option value="2" <?php echo $sort_price == "2" ? 'selected' : ''; ?>>Highest to Lowest</option>
            </select>
        </div>
    </div>
</form>
