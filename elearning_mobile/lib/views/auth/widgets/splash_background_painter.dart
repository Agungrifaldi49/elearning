import 'dart:math' as math;
import 'package:flutter/material.dart';

class SplashBackgroundPainter extends CustomPainter {
  final double pulseProgress;
  final double gridOpacity;

  SplashBackgroundPainter({
    required this.pulseProgress,
    required this.gridOpacity,
  });

  @override
  void paint(Canvas canvas, Size size) {
    if (gridOpacity <= 0) return;

    final center = Offset(size.width / 2, size.height * 0.42);
    final maxRadius = math.sqrt(size.width * size.width + size.height * size.height) * 0.5;

    // 1. Draw subtle concentric energy pulse rings
    final pulsePaint = Paint()
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.5;

    for (int i = 0; i < 3; i++) {
      double ringProgress = (pulseProgress + (i * 0.33)) % 1.0;
      double currentRadius = 60 + (ringProgress * (maxRadius - 60));
      double alpha = (1.0 - ringProgress) * 0.25 * gridOpacity;

      pulsePaint.color = Colors.white.withValues(alpha: alpha.clamp(0.0, 1.0));
      canvas.drawCircle(center, currentRadius, pulsePaint);
    }

    // 2. Draw subtle precision tech grid lines
    final linePaint = Paint()
      ..color = Colors.white.withValues(alpha: 0.05 * gridOpacity)
      ..strokeWidth = 1.0;

    double step = 40.0;
    for (double x = 0; x < size.width; x += step) {
      canvas.drawLine(Offset(x, 0), Offset(x, size.height), linePaint);
    }
    for (double y = 0; y < size.height; y += step) {
      canvas.drawLine(Offset(0, y), Offset(size.width, y), linePaint);
    }

    // 3. Glowing micro-data dots around center tech hub
    final dotPaint = Paint()
      ..style = PaintingStyle.fill
      ..color = Colors.cyanAccent.withValues(alpha: 0.35 * gridOpacity);

    for (int i = 0; i < 8; i++) {
      double angle = (i * math.pi / 4) + (pulseProgress * 2 * math.pi);
      double distance = 110 + 15 * math.sin(pulseProgress * 2 * math.pi + i);
      double dx = center.dx + distance * math.cos(angle);
      double dy = center.dy + distance * math.sin(angle);
      canvas.drawCircle(Offset(dx, dy), 2.5, dotPaint);
    }
  }

  @override
  bool shouldRepaint(covariant SplashBackgroundPainter oldDelegate) {
    return oldDelegate.pulseProgress != pulseProgress ||
        oldDelegate.gridOpacity != gridOpacity;
  }
}
