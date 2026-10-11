<?php
$h1 = '$2y$10$ffyyoF7s5fdEyjECXNCSk.LGoWI8Td5pMvn0AM3U5ieZL3rQy3Nq6';
$h2 = '$2y$10$Zv5aC7vSYBo2xhEzpJaTd.1UDtgoPox4tn4QfEy5X78ppMAyvXZA6';
$words = ['123456', '12345678', 'agung', 'agung123', 'siswa', 'siswa123', '522402055', 'aggrfld23', 'smkmh2024', 'smkmhc@2011', 'admin', 'password'];
foreach ($words as $w) {
    if (password_verify($w, $h1)) echo "h1 matches: $w\n";
    if (password_verify($w, $h2)) echo "h2 matches: $w\n";
}
echo "Done.\n";
