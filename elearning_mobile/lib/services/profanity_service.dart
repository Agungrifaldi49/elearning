class ProfanityService {
  static const List<String> _badWords = [
    'anjing', 'anjrit', 'anjir', 'anjay', 'asu', 'babi', 'bangsat',
    'kontol', 'memek', 'pantek', 'pepek', 'itil', 'peler', 'ngentot', 'ngentod',
    'jembut', 'goblok', 'goblog', 'tolol', 'kampret', 'bajingan', 'bego', 'begok',
    'jancok', 'jancuk', 'modar', 'perek', 'lonte', 'silit', 'sange', 'tetek',
    'toket', 'ngaceng', 'crot', 'bokep', 'porno', 'fuck', 'shit', 'bitch',
    'asshole', 'bastard', 'cunt', 'dick', 'pussy', 'cock', 'nude', 'slut', 'whore',
    'membunuh', 'pembantaian', 'bacok', 'gorok'
  ];

  static String filter(String? text) {
    if (text == null || text.trim().isEmpty) return '';
    String filtered = text;

    for (final word in _badWords) {
      final pattern = RegExp(RegExp.escape(word), caseSensitive: false);
      filtered = filtered.replaceAllMapped(pattern, (match) {
        final matchedStr = match.group(0) ?? '';
        if (matchedStr.length <= 2) {
          return '*' * matchedStr.length;
        }
        return matchedStr[0] + ('*' * (matchedStr.length - 2)) + matchedStr[matchedStr.length - 1];
      });
    }

    return filtered;
  }
}
