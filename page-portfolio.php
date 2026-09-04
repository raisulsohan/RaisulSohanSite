<?php
/**
 * Template Name: Portfolio
 *
 * Multidisciplinary portfolio showcase: Video Editing, Motion Animation,
 * Web Development, and Browser Extensions / Plugins / Scripts.
 *
 * Features interactive Case Study Pop-up Modals, screenshot support,
 * and direct preview links. Fully localized for Bengali and English.
 *
 * @package raisul-sohan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$rs_is_en = rs_is_en();
$rs_hero  = rs_hero_image();

/* ---- Portfolio Projects & Case Studies Data ---- */
$projects = array(
	// Web Development (Featured First)
	array(
		'id'          => 'bichitro-biggan',
		'category'    => 'web',
		'type_bn'     => 'ম্যাগাজিন ও ডিজিটাল পাবলিকেশন থিম',
		'type_en'     => 'Digital Magazine Platform',
		'badge_bn'    => 'ফ্ল্যাগশিপ প্রজেক্ট',
		'badge_en'    => 'Flagship Project',
		'title_bn'    => 'বিচিত্র বিজ্ঞান — জিরো-প্লাগিন ডিজিটাল সায়েন্স ম্যাগাজিন',
		'title_en'    => 'Bichitro Biggan — Zero-Plugin Science Magazine Platform',
		'summary_bn'  => 'বাংলা বিজ্ঞান ম্যাগাজিনের জন্য স্ক্র্যাচ থেকে তৈরি অত্যাধুনিক, সুপারফাস্ট এবং সম্পূর্ণ জিরো-প্লাগিন ক্লাসিক ওয়ার্ডপ্রেস থিম। এতে রয়েছে ৮-কার্ড হিরো মোজাইক, সিনেমেটিক ভিডিও পপ-আপ, লাইভ সার্চ ও নেটিভ এসইও ইঞ্জিন।',
		'summary_en'  => 'A modern, ultra-fast, zero-plugin classic WordPress theme engineered from scratch for a Bengali science magazine. Features an 8-card mosaic hero, native AJAX reading modal, built-in SEO engine, and dynamic video popups.',
		'role_bn'     => 'একক ডিজাইনার ও ফুলস্ট্যাক থিম ডেভেলপার',
		'role_en'     => 'Lead Designer & Full-Stack Theme Developer',
		'context_bn'  => 'বিজ্ঞান ম্যাগাজিন ও ডিজিটাল প্রকাশনা • ভার্সন ৭.৩',
		'context_en'  => 'Science Magazine & Digital Publication • v7.3',
		'challenge_bn'=> 'অনলাইন নিউজ ও ম্যাগাজিন সাইটগুলোতে সাধারণত ২০-৩০টি ভারী প্লাগিন ও থার্ড-পার্টি স্ক্রিপ্ট ব্যবহারের ফলে পেজ লোডিং অনেক স্লো হয়ে যায়। কোনো এক্সটার্নাল প্লাগিন ও কোনো jQuery ছাড়াই ১০০% নেটিভ পিএইচপি ও ভ্যানিলা জাভাস্ক্রিপ্টে একটি পূর্ণাঙ্গ আধুনিক ম্যাগাজিনের সব ফিচার (রিয়েল-টাইম সার্চ, সিনেমেটিক ভিডিও পপ-আপ, রিডিং মডাল, বুকমার্ক স্লাইড ড্রয়ার, এসইও মেটাবক্স) তৈরি করাই ছিল মূল চ্যালেঞ্জ।',
		'challenge_en'=> 'Online publications traditionally suffer from severe speed penalties due to dependency on dozens of plugins. The challenge was architecting an expansive, feature-dense publication platform with zero third-party plugins and zero jQuery—relying purely on native WordPress PHP APIs and clean ES6+ JavaScript.',
		'solution_bn' => 'সম্পূর্ণ কাস্টম আর্কিটেকচারে থিমটি স্ক্র্যাচ থেকে তৈরি করা হয়েছে। ফিগমা-স্টাইল ৮-কার্ড হিরো মোজাইক গ্রিড, নেটিভ এজেক্স রিডিং মডাল, লোকালস্টোরেজ ভিত্তিক বুকমার্ক স্লাইড-আউট ড্রয়ার, ইউটিউব রেসপনসিভ ভিডিও পপ-আপ (১৬:৯, ৯:১৬, ৪:৫, ১:১) এবং ইয়োস্টের বিকল্প হিসেবে নিজস্ব লাইভ গুগল প্রিভিউ এসইও মেটাবক্স ও JSON-LD স্কিমা ইঞ্জিন তৈরি করা হয়েছে। সাথে রয়েছে স্বয়ংক্রিয় গিটহাব আপডেটার ও লাইসেন্সিং সিস্টেম।',
		'solution_en' => 'Engineered a bespoke zero-plugin foundation from the ground up: an 8-card hero mosaic grid, native AJAX reading modal with zero page reloads, a slide-out localStorage bookmark drawer, dynamic aspect-ratio YouTube video popups, and an in-editor Google search snippet preview with automated JSON-LD schema. Integrated a native GitHub auto-updater and license manager.',
		'highlights_bn'=> array(
			'০টি এক্সটার্নাল প্লাগিন — এসইও, লাইভ সার্চ, বুকমার্ক ও মডাল সম্পূর্ণ নেটিভ কোডে নির্মিত',
			'ফিগমা ও ম্যাগাজিন লেআউট: ৮-কার্ড হিরো মোজাইক এবং রেসপনসিভ মাল্টি-কলাম ক্যাটাগরি গ্রিড',
			'ইউটিউবের জন্য সিনেমেটিক ভিডিও পপ-আপ (১৬:৯, ৯:১৬, ৪:৫, ১:১ অ্যাসপেক্ট রেশিও সাপোর্ট)',
			'ওয়ার্ডপ্রেস ক্লাসিক এডিটরে লাইভ গুগল স্নিপেট প্রিভিউ সহ নিজস্ব এসইও মেটাবক্স ও JSON-LD স্কিমা',
			'নেটিভ গিটহাব অটো-আপডেটার — ড্যাশবোর্ড থেকেই এক ক্লিকে থিম আপডেট পাওয়ার সুবিধা',
			'কোনো jQuery নেই — সম্পূর্ণ আধুনিক ভ্যানিলা জাভাস্ক্রিপ্ট (ES6+) ও সিএসএস ভ্যারিয়েবল আর্কিটেকচার'
		),
		'highlights_en'=> array(
			'Zero-Plugin Architecture: Native built-in SEO, live search, bookmarks, and modals without third-party plugins',
			'Figma Mac & Magazine layout: 8-card hero mosaic and responsive multi-column category grids',
			'Cinematic video popups supporting dynamic aspect ratios (16:9, 9:16, 4:5, 1:1) from YouTube',
			'In-editor Google search snippet preview, Open Graph cards, and native JSON-LD schema generator',
			'Native GitHub Auto-Updater delivering instant dashboard updates via commit SHA verification',
			'Pure Vanilla ES6+ JavaScript (zero jQuery) and modern CSS variable architecture for sub-second speeds'
		),
		'tags'        => array( 'WordPress', 'PHP 7.4+', 'Vanilla JS (ES6+)', 'CSS3 Grid', 'Built-in SEO', 'Auto-Updater' ),
		'accent'      => '#00b894',
		'icon'        => 'code',
		'image'       => 'https://bichitrobiggan.com/wp-content/uploads/2026/08/logo-wide.png',
		'image_fit'   => 'contain',
		'action_type' => 'web',
		'action_bn'   => 'লাইভ সাইট দেখুন',
		'action_en'   => 'Visit Live Website',
		'direct_url'  => 'https://bichitrobiggan.com',
		'github_url'  => 'https://github.com/raisulsohan/BichitroBiggan',
	),
	array(
		'id'          => 'theme-dev',
		'category'    => 'web',
		'type_bn'     => 'ব্যক্তিগত ব্লগ ও সাহিত্য থিম',
		'type_en'     => 'Personal Bespoke Theme',
		'badge_bn'    => 'ওপেন সোর্স থিম',
		'badge_en'    => 'Open Source Theme',
		'title_bn'    => 'জিরো-প্লাগিন বেস্পোক ওয়ার্ডপ্রেস থিম আর্কিটেকচার',
		'title_en'    => 'Zero-Plugin Bespoke WordPress Theme Architecture',
		'summary_bn'  => 'কোনো থার্ড-পার্টি প্লাগিন ছাড়াই নির্মিত সুপারফাস্ট ব্যক্তিগত ওয়েবসাইট থিম। এতে রয়েছে এজেক্স নেভিগেশন, ডার্ক মোড ও ডায়নামিক কালার প্যালেট জেনারেশন।',
		'summary_en'  => 'An ultra-fast, zero-plugin custom WordPress theme featuring seamless AJAX pagination, distraction-free reading modal, and WCAG-compliant dynamic theming.',
		'role_bn'     => 'ফুলস্ট্যাক থিম আর্কিটেক্ট',
		'role_en'     => 'Full-Stack Theme Architect',
		'context_bn'  => 'ব্যক্তিগত সাহিত্য ও ব্লগ পোর্টাল • ভার্সন ৭.৪',
		'context_en'  => 'Personal Literature & Blog Portal • v7.4',
		'challenge_bn'=> 'সাধারণ ওয়ার্ডপ্রেস সাইটে ২০-৩০টি ভারী প্লাগিন ব্যবহারের ফলে সাইট ভারী ও স্লো হয়ে যায়। কোনো প্লাগিন ছাড়া সম্পূর্ণ নেটিভ পিএইচপি ও জাভাস্ক্রিপ্টে সব ফিচার তৈরি করা।',
		'challenge_en'=> 'Overcoming standard WordPress bloat caused by plugin dependence, achieving instant loading without sacrificing advanced interactive features.',
		'solution_bn' => 'সম্পূর্ণ বেস্পোক ফাংশনস আর্কিটেকচার তৈরি করা হয়েছে। বিল্ট-ইন এজেক্স ফ্র্যাগমেন্ট রেন্ডারিং, লোকালস্টোরেজ ডার্ক মোড, এসইও মেটাডাটা ও নিজস্ব গিটহাব অটো-আপডেটার যুক্ত।',
		'solution_en' => 'Engineered a bespoke zero-plugin framework: built-in lightweight REST search, local browser state persistence, WCAG 4.5:1 dynamic theming, and GitHub self-updater.',
		'highlights_bn'=> array(
			'০টি এক্সটার্নাল প্লাগিন — সম্পূর্ণ নিরাপদ ও স্বাবলম্বী কোডবেস',
			'১০০/১০০ গুগল পেজস্পিড ও কোর ওয়েব ভাইটালস স্কোর',
			'স্মুথ রিডিং মডাল যা ব্রাউজার হিস্ট্রি পুশ-স্টেট সাপোর্ট করে',
			'দ্বিভাষিক মাল্টিসাইট পারমালিংক অপ্টিমাইজেশন (/blog প্রিফিক্স ক্লিন)'
		),
		'highlights_en'=> array(
			'Zero third-party plugins: clean, highly auditable codebase',
			'100/100 Google PageSpeed & Core Web Vitals across mobile and desktop',
			'Distraction-free reading modal with pushState browser history synchronization',
			'Custom rewrite system eliminating multisite /blog prefix issues'
		),
		'tags'        => array( 'WordPress', 'PHP 8', 'Vanilla JS', 'Semantic CSS', 'REST API' ),
		'accent'      => '#0984e3',
		'icon'        => 'code',
		'image'       => get_template_directory_uri() . '/screenshot.png',
		'image_fit'   => 'cover',
		'action_type' => 'web',
		'action_bn'   => 'লাইভ সাইট দেখুন',
		'action_en'   => 'Visit Live Site',
		'direct_url'  => home_url( '/' ),
		'github_url'  => 'https://github.com/raisulsohan/RaisulSohanSite',
	),
	array(
		'id'          => 'digital-shelf',
		'category'    => 'web',
		'type_bn'     => 'ওয়েব অ্যাপ্লিকেশন',
		'type_en'     => 'Web Application',
		'badge_bn'    => 'সিএসএস ইন্টারঅ্যাকশন',
		'badge_en'    => 'CSS Interaction',
		'title_bn'    => 'ইন্টারেক্টিভ ডিজিটাল বুকশেল্ফ ও রিডিং পোর্টাল',
		'title_en'    => 'Interactive Digital Bookshelf & Reading Hub',
		'summary_bn'  => 'কোনো ভারী ইমেজ ছাড়াই পিওর সিএসএস স্পাইন জেনারেশন, লাইভ ইনস্ট্যান্ট ফিল্টারিং ও সার্চ সহ রেসপনসিভ বুকশেল্ফ ইন্টারফেস।',
		'summary_en'  => 'Procedural CSS book spine rendering without heavy images, instant author & genre filtering, and client-side search with near-zero latency.',
		'role_bn'     => 'ফ্রন্টএন্ড ইঞ্জিনিয়ার',
		'role_en'     => 'Frontend UI Engineer',
		'context_bn'  => 'বই পড়ার লাইব্রেরি ও বুক ট্র্যাকার',
		'context_en'  => 'Native Reading Tracker & Library Shelf',
		'challenge_bn'=> 'শত শত বইয়ের জন্য কভার ইমেজ লোড করলে পেজের ওজন অনেক বেড়ে যায়। ইমেজ ছাড়া কীভাবে একটি সত্যিকারের কাঠের বুকশেল্ফের অনুভূতি তৈরি করা যায়?',
		'challenge_en'=> 'Displaying an extensive library of books without the bandwidth penalty of heavy cover images while maintaining the warmth of a real wooden bookshelf.',
		'solution_bn' => 'বইয়ের জঁরা (Genre) থেকে অ্যালগরিদমিক কালার হ্যাশ এবং নামের দৈর্ঘ্য থেকে বইয়ের উচ্চতা হিসাব করে পিওর সিএসএস দিয়ে স্পাইন রেন্ডার করা হয়েছে।',
		'solution_en' => 'Generated procedural spine colors using CRC32 hash algorithms mapped to HSL palettes, and calculated realistic spine heights based on character counts.',
		'highlights_bn'=> array(
			'কোনো এক্সটার্নাল ইমেজ ছাড়াই ০ কিলোবাইট গ্রাফিক্স লোড',
			'ক্লায়েন্ট-সাইড লাইভ ফিল্টার ও অটো-সাজেস্ট সার্চ',
			'মোবাইল স্ক্রিনে প্রতি সারিতে ঠিক ৫টি বইয়ের অপ্টিমাইজড গ্রিড',
			'ক্লাসিক লিস্ট ভিউ ও বুকশেল্ফ ভিউয়ের মধ্যে ইনস্ট্যান্ট টগল'
		),
		'highlights_en'=> array(
			'Zero external cover images: virtually instantaneous load times',
			'Real-time client-side genre/author filtering with auto-suggestions',
			'Tailored mobile layout showing exactly 5 balanced spines per row',
			'Seamless one-click toggle between classic table list and visual shelf'
		),
		'tags'        => array( 'JavaScript', 'CSS Grid', 'Dynamic HSL', 'UX Design' ),
		'accent'      => '#00b894',
		'icon'        => 'layout',
		'image'       => '',
		'action_type' => 'web',
		'action_bn'   => 'বুকশেল্ফে যান',
		'action_en'   => 'Open Bookshelf',
		'direct_url'  => home_url( '/book-list/' ),
	),

	// Video & Animation
	array(
		'id'          => 'docu-story',
		'category'    => 'video',
		'type_bn'     => 'ভিডিও এডিটিং',
		'type_en'     => 'Video Editing',
		'badge_bn'    => 'ফিচার্ড কেস স্টাডি',
		'badge_en'    => 'Featured Case Study',
		'title_bn'    => 'ডকুমেন্টারি স্টোরিটেলিং ও সিনেমাটিক এডিটিং',
		'title_en'    => 'Documentary Narrative & Cinematic Edit',
		'summary_bn'  => 'ভিজ্যুয়াল পেসিং, গভীর সাউন্ড ডিজাইন ও সিনেমাটিক কালার গ্রেডিংয়ের সমন্বয়ে নির্মিত দীর্ঘ ডকু-ফিচার এডিটিং। প্রতিটি কাটে আবেগের ধারাবাহিকতা বজায় রাখা হয়েছে।',
		'summary_en'  => 'A long-form documentary edit crafted with dynamic visual pacing, atmospheric sound design, and cinematic color grading with emotional continuity.',
		'role_bn'     => 'প্রধান ভিডিও এডিটর ও কালারিস্ট',
		'role_en'     => 'Lead Video Editor & Colorist',
		'context_bn'  => 'ইনডিপেনডেন্ট ফিচার ডকুফিল্ম • ৩০ মিনিট',
		'context_en'  => 'Independent Feature Doc • 30 mins',
		'challenge_bn'=> 'কয়েক ঘণ্টার অসংগঠিত ইন্টারভিউ ফুটেজ ও বি-রোল থেকে একটি আকর্ষণীয় ৩-অ্যাক্ট স্টোরিলাইন তৈরি করা এবং ভিন্ন ভিন্ন ক্যামেরার কালার প্রোফাইলকে ম্যাচ করানো ছিল মূল চ্যালেঞ্জ।',
		'challenge_en'=> 'Distilling dozens of hours of unscripted interviews and handheld b-roll into a cohesive 3-act narrative, while harmonizing color profiles across mixed camera setups.',
		'solution_bn' => 'আবেগের ওঠানামার ওপর ভিত্তি করে পেসিং সাজানো হয়। ডাভিঞ্চি রিজলভে কাস্টম লুত (LUT) তৈরি করে ফিল্মি টেক্সচার আনা হয় এবং সাউন্ড এফেক্টের নিখুঁত লেয়ারিংয়ে দৃশ্যগুলোকে জীবন্ত করা হয়।',
		'solution_en' => 'Constructed an emotionally driven pacing arc. Created bespoke LUT profiles in DaVinci Resolve for a tactile film look and sculpted a rich, multi-layered soundscape.',
		'highlights_bn'=> array(
			'ইন্টারভিউ ও বি-রোলের মধ্যে মসৃণ রিদমিক ট্রানজিশন',
			'১০-বিট কালার কারেকশন ও স্কিন-টোন প্রোটেকশন',
			'পূর্ণাঙ্গ সাউন্ড ডিজাইন (Foley, Ambience & Score mixing)',
			'ইউটিউব ও ফেস্টিভ্যাল উভয় প্ল্যাটফর্মের জন্য অপ্টিমাইজড কালার ডেলিভারি'
		),
		'highlights_en'=> array(
			'Seamless rhythmic transitions between dialogue and cinematic b-roll',
			'Precision 10-bit color grading with accurate skin-tone tracking',
			'Full atmospheric audio design (Foley, room ambience, dynamic score mixing)',
			'Dual delivery mastered for high-retention streaming and festival projection'
		),
		'tags'        => array( 'Premiere Pro', 'DaVinci Resolve', 'Sound Design', 'Storyboarding' ),
		'accent'      => '#e17055',
		'icon'        => 'video',
		'image'       => '',
		'action_type' => 'video',
		'action_bn'   => 'মূল ভিডিও দেখুন',
		'action_en'   => 'Watch Main Video',
		'direct_url'  => 'https://www.youtube.com',
	),
	array(
		'id'          => 'motion-explainer',
		'category'    => 'video',
		'type_bn'     => 'মোশন অ্যানিমেশন',
		'type_en'     => 'Motion Animation',
		'badge_bn'    => '২ডি অ্যানিমেশন',
		'badge_en'    => '2D Animation',
		'title_bn'    => '২ডি ক্যারেক্টার ও এক্সপ্লেইনার মোশন গ্রাফিক্স',
		'title_en'    => '2D Character & Explainer Motion Graphics',
		'summary_bn'  => 'জটিল কনসেপ্টকে সহজ ও সাবলীলভাবে বোঝাতে কাস্টম ভেক্টর ইলাস্ট্রেশন ও রিদমিক কিফ্রেম অ্যানিমেশন। সাউন্ড ইফেক্টের সাথে ভিজ্যুয়াল ট্রানজিশনের নিখুঁত টাইমিং।',
		'summary_en'  => 'Custom vector illustrations brought to life through fluid keyframe animation, translating complex concepts into engaging, digestible visual stories.',
		'role_bn'     => 'মোশন ডিজাইনার ও অ্যানিমেটর',
		'role_en'     => 'Motion Designer & Animator',
		'context_bn'  => 'প্রোডাক্ট এক্সপ্লেইনার ভিডিও • ৯০ সেকেন্ড',
		'context_en'  => 'Product Explainer Video • 90 secs',
		'challenge_bn'=> 'একটি জটিল সফটওয়্যার কীভাবে কাজ করে তা প্রথম ৩০ সেকেন্ডে সাধারণ দর্শকের বোধগম্য করে তোলা এবং ব্র্যান্ডের কালার প্যালেট বজায় রেখে স্ক্রিনে আকর্ষণ তৈরি করা।',
		'challenge_en'=> 'Demystifying complex software functionality within the first 30 seconds while keeping viewers visually engaged using branded color harmonies.',
		'solution_bn' => 'ইলাস্ট্রেটরে প্রতিটি ক্যারেক্টার ও সিন আলাদা লেয়ারে ডিজাইন করে আফটার ইফেক্টসে রিগিং করা হয়। গ্রাফ এডিটর ব্যবহার করে ইলাস্টিক ইজিং ও স্ন্যাপি মোশন তৈরি করা হয়।',
		'solution_en' => 'Designed modular vector scenes in Illustrator and rigged characters in After Effects. Polished movement using custom velocity curves for snappy, organic weight.',
		'highlights_bn'=> array(
			'কাস্টম ভেক্টর ক্যারেক্টার রিগিং ও ফেসিয়াল এক্সপ্রেশন',
			'গ্রাফ এডিটর দিয়ে নিখুঁত ইজ-ইন/ইজ-আউট মোশন কার্ভ',
			'ভিজ্যুয়াল মেটাফোরের মাধ্যমে জটিল ডেটা ফ্লোর সহজ উপস্থাপন',
			'অডিও বিটের সাথে মিল রেখে টাইপোগ্রাফিক এনিমেশন'
		),
		'highlights_en'=> array(
			'Custom vector character rigging with responsive secondary motion',
			'Precision velocity curves for springy, high-energy animation feel',
			'Visual metaphors effectively conveying technical data workflows',
			'Synchronized kinetic typography matching voiceover cadence'
		),
		'tags'        => array( 'After Effects', 'Illustrator', '2D Motion', 'Character Rigging' ),
		'accent'      => '#6c5ce7',
		'icon'        => 'animation',
		'image'       => '',
		'action_type' => 'video',
		'action_bn'   => 'মোশন রিল দেখুন',
		'action_en'   => 'View Motion Reel',
		'direct_url'  => 'https://vimeo.com',
	),
	array(
		'id'          => 'brand-promo',
		'category'    => 'video',
		'type_bn'     => 'কমার্শিয়াল রিল',
		'type_en'     => 'Commercial Reel',
		'badge_bn'    => 'সোশ্যাল ক্যাম্পেইন',
		'badge_en'    => 'Social Campaign',
		'title_bn'    => 'হাই-এনার্জি কমার্শিয়াল ও সোশ্যাল মিডিয়া প্রোমো',
		'title_en'    => 'High-Energy Commercial & Promo Reel',
		'summary_bn'  => 'সোশ্যাল মিডিয়া ও ব্রান্ড ক্যাম্পেইনের উপযোগী স্ন্যাপি ট্রানজিশন, কাইনেটিক টাইপোগ্রাফি ও মিউজিক বিট-সিঙ্ক এডিটিং যা প্রথম ৩ সেকেন্ডেই দর্শককে ধরে রাখে।',
		'summary_en'  => 'Fast-paced rhythmic editing, kinetic typography, and snappy visual transitions tailored for social campaigns to maximize audience retention.',
		'role_bn'     => 'কমার্শিয়াল ভিডিও এডিটর',
		'role_en'     => 'Commercial Video Editor',
		'context_bn'  => 'ব্র্যান্ড ক্যাম্পেইন ও রিলস (৯:১৬ এবং ১৬:৯)',
		'context_en'  => 'Brand Campaign & Reels (9:16 & 16:9)',
		'challenge_bn'=> 'টিকটক, ইনস্টাগ্রাম রিলস এবং ইউটিউব শর্টসে দর্শকদের মাইক্রো-অ্যাটেনশন স্প্যান ধরে রাখা এবং একই সাথে প্রোডাক্টের ভ্যালু প্রপোজিশন পরিষ্কার রাখা।',
		'challenge_en'=> 'Overcoming short attention spans on vertical video platforms while communicating value propositions clearly before the drop-off window.',
		'solution_bn' => 'প্রতি সেকেন্ডে ভিজ্যুয়াল রিফ্রেশ ও সাউন্ড রাইজার্স ব্যবহার করা হয়েছে। মিউজিকের ড্রপ এবং বিটের সাথে মিল রেখে কাট, জুম ও টেক্সট পপ-আপ প্রয়োগ করা হয়েছে।',
		'solution_en' => 'Engineered audio-visual hooks within the first 3 seconds, layering whooshes, impacts, and rhythmic micro-zooms synced tightly with tempo drops.',
		'highlights_bn'=> array(
			'প্রথম ৩ সেকেন্ডে ড্রপ-অফ রোধকারী ভিজ্যুয়াল হুক',
			'রিলস ও টিকটকের জন্য অপ্টিমাইজড ৯:১৬ ফ্রেম কম্পোজিশন',
			'অটো-ক্যাপশন ও স্ন্যাপি টেক্সট অ্যানিমেশন',
			'অর্গানিক কালার পপ ও হাই-কন্ট্রাস্ট গ্রেডিং'
		),
		'highlights_en'=> array(
			'Hook-centric structure designed to reduce first-3-seconds swipe-away',
			'Composition optimized specifically for mobile 9:16 vertical viewports',
			'High-impact kinetic text overlays for muted video playback',
			'Vibrant, color-popped grading optimized for mobile screens'
		),
		'tags'        => array( 'After Effects', 'Premiere Pro', 'Kinetic Typography', 'Reels' ),
		'accent'      => '#d63031',
		'icon'        => 'video',
		'image'       => '',
		'action_type' => 'video',
		'action_bn'   => 'প্রোমো দেখুন',
		'action_en'   => 'Watch Promo',
		'direct_url'  => 'https://www.youtube.com',
	),

	// Extensions, Plugins & Scripts
	array(
		'id'          => 'chrome-ext',
		'category'    => 'tools',
		'type_bn'     => 'ক্রোম এক্সটেনশন',
		'type_en'     => 'Chrome Extension',
		'badge_bn'    => 'Manifest V3',
		'badge_en'    => 'Manifest V3',
		'title_bn'    => 'স্মার্ট ওয়ার্কফ্লো ও ট্যাব ম্যানেজার (Manifest V3)',
		'title_en'    => 'Smart Workflow & Tab Manager (Manifest V3)',
		'summary_bn'  => 'ব্রাউজিং প্রোডাক্টিভিটি বাড়াতে তৈরি আধুনিক Manifest V3 এক্সটেনশন। কিবোর্ড শর্টকাট, কুইক গ্রুপ ও সেশন মেমোরি সংরক্ষণ ফিচার যুক্ত।',
		'summary_en'  => 'A high-performance Chrome extension built on Manifest V3 for organizing active workflows, quick tab grouping, and instant session recall.',
		'role_bn'     => 'এক্সটেনশন ডেভেলপার',
		'role_en'     => 'Extension Developer',
		'context_bn'  => 'ব্রাউজার প্রোডাক্টিভিটি ইউটিলিটি',
		'context_en'  => 'Browser Productivity Utility',
		'challenge_bn'=> 'ক্রোমের নতুন Manifest V3 আর্কিটেকচারে ব্যাকগ্রাউন্ড সার্ভিস ওয়ার্কার টার্মিনেশনের চ্যালেঞ্জ মোকাবিলা করে রিয়েল-টাইম ট্যাব ট্র্যাকিং নিশ্চিত করা।',
		'challenge_en'=> 'Working within Manifest V3 ephemeral service worker lifecycles while maintaining persistent session state and zero memory leaks.',
		'solution_bn' => 'Chrome Storage API এবং ডিক্লারেটিভ ইভেন্ট লিসেনার ব্যবহার করা হয়েছে। ব্যাকগ্রাউন্ডে কম মেমোরি খরচ করে কিবোর্ড কমান্ডে ট্যাব রিনেম ও গ্রুপ করা যায়।',
		'solution_en' => 'Implemented efficient event-driven service workers paired with IndexedDB/Chrome Storage, enabling lightning-fast keyboard-first workspace switching.',
		'highlights_bn'=> array(
			'গুগল Manifest V3 স্ট্যান্ডার্ডের সাথে ১০০% সামঞ্জস্যপূর্ণ',
			'র‌্যাম ব্যবহার কম রাখার জন্য স্লিপ-ট্যাব মেকানিজম',
			'কাস্টম কিবোর্ড হটকিজের মাধ্যমে মাউস-লেস নেভিগেশন',
			'এক ক্লিকে সেশন ব্যাকআপ ও জেসন এক্সপোর্ট/ইমপোর্ট'
		),
		'highlights_en'=> array(
			'100% compliant with Google Chrome Manifest V3 specifications',
			'Memory-saving tab suspension engine for low-RAM machines',
			'Full keyboard shortcut palette for mouseless navigation',
			'Instant one-click workspace snapshot export and import'
		),
		'tags'        => array( 'Chrome API', 'Manifest V3', 'JavaScript', 'Service Worker' ),
		'accent'      => '#fdcb6e',
		'icon'        => 'extension',
		'image'       => '',
		'action_type' => 'code',
		'action_bn'   => 'সোর্স কোড (GitHub)',
		'action_en'   => 'View Source on GitHub',
		'direct_url'  => 'https://github.com/raisulsohan',
	),
	array(
		'id'          => 'wp-optimizer',
		'category'    => 'tools',
		'type_bn'     => 'ওয়ার্ডপ্রেস প্লাগিন',
		'type_en'     => 'WordPress Plugin',
		'badge_bn'    => 'পারফরম্যান্স টুল',
		'badge_en'    => 'Performance Tool',
		'title_bn'    => 'ব্লট-রিমুভার ও অ্যাসেট পারফরম্যান্স মডিউল',
		'title_en'    => 'Zero-Bloat Asset & Script Performance Module',
		'summary_bn'  => 'অপ্রয়োজনীয় ডিফল্ট স্ক্রিপ্ট ছাঁটাই, ফন্ট প্রি-লোডিং এবং রেন্ডার-ব্লকিং অ্যাসেট অপ্টিমাইজ করার কাস্টম মডিউল যা পেজস্পিড ১০০/১০০ করে।',
		'summary_en'  => 'Custom utility module engineered to prune unused core assets, configure font preloading, and eliminate render-blocking resources for 100/100 Core Web Vitals.',
		'role_bn'     => 'প্লাগিন ডেভেলপার',
		'role_en'     => 'WordPress Plugin Developer',
		'context_bn'  => 'কোর পারফরম্যান্স ও অ্যাসেট কন্ট্রোল',
		'context_en'  => 'Core Performance & Asset Optimizer',
		'challenge_bn'=> 'ওয়ার্ডপ্রেস কোর নিজে থেকেই অনেক অব্যবহৃত সিএসএস/জেএস (যেমন: ইমোজি স্ক্রিপ্ট, গুটেনবার্গ ব্লক স্টাইলস) লোড করে পেজ সাইজ বড় করে ফেলে।',
		'challenge_en'=> 'WordPress default core scripts and styles (emojis, block CSS on non-block pages) add unnecessary HTTP requests and inflate load latency.',
		'solution_bn' => 'একটি হাইপার-লাইটওয়েট ড্রপ-ইন প্লাগিন তৈরি করা হয়েছে যা অপ্রয়োজনীয় হ্যান্ডেলগুলো আন-রেজিস্টার করে এবং ফন্ট ফাইলগুলো আগেই প্রিলোড করে।',
		'solution_en' => 'Constructed a conditional asset pipeline that deregisters unused core assets, inlines critical render paths, and preloads custom typography.',
		'highlights_bn'=> array(
			'HTTP রিকোয়েস্ট সংখ্যা ৪০% পর্যন্ত হ্রাস',
			'ইনলাইন ক্রিটিক্যাল সিএসএস ও ফন্ট প্রিলোড কনফিগারেশন',
			'অ্যাডমিন প্যানেলে কোনো বাড়তি বোঝা বা ভারী সেটিংস পেজ ছাড়া ক্লিন কোড',
			'সব ধরনের আধুনিক ওয়ার্ডপ্রেস থিমের সাথে প্লাগ-অ্যান্ড-প্লে সামঞ্জস্য'
		),
		'highlights_en'=> array(
			'Reduces front-end HTTP request count by up to 40%',
			'Automates font preloading and eliminates render-blocking tags',
			'Zero administrative overhead: runs cleanly as a lean drop-in utility',
			'Universal compatibility with modern standard-compliant themes'
		),
		'tags'        => array( 'PHP', 'WordPress Hooks', 'Core Web Vitals', 'Asset Dequeue' ),
		'accent'      => '#6c5ce7',
		'icon'        => 'plugin',
		'image'       => '',
		'action_type' => 'code',
		'action_bn'   => 'গিটহাব রিপোজিটরি',
		'action_en'   => 'GitHub Repository',
		'direct_url'  => 'https://github.com/raisulsohan',
	),
	array(
		'id'          => 'media-script',
		'category'    => 'tools',
		'type_bn'     => 'অটোমেশন স্ক্রিপ্ট',
		'type_en'     => 'Automation Script',
		'badge_bn'    => 'পাইথন ও ব্যাশ',
		'badge_en'    => 'Python & CLI',
		'title_bn'    => 'মিডিয়া ট্রান্সকোডিং ও ব্যাকআপ অটোমেশন টুল',
		'title_en'    => 'Batch Media Transcoding & Asset Sync Script',
		'summary_bn'  => 'FFmpeg এবং পাইথনের সাহায্যে স্বয়ংক্রিয়ভাবে হাই-রেজোলিউশন ভিডিও কম্প্রেস করা, থাম্বনেইল তৈরি এবং ক্লাউড স্টোরেজে সিঙ্ক করার অটোমেশন স্ক্রিপ্ট।',
		'summary_en'  => 'Python & Shell automation script leveraging FFmpeg for batch lossless video compression, multi-ratio thumbnail generation, and cloud sync.',
		'role_bn'     => 'স্ক্রিপ্ট ডেভেলপার',
		'role_en'     => 'Automation Engineer',
		'context_bn'  => 'ভিডিও প্রোডাকশন ওয়ার্কফ্লো পাইপলাইন',
		'context_en'  => 'Video Post-Production Automation',
		'challenge_bn'=> 'প্রতিবার ভিডিও এডিটের পর ম্যানুয়ালি একাধিক রেজোলিউশনে রেন্ডার করা, থাম্বনেইল ফ্রেম এক্সট্র্যাক্ট করা এবং ব্যাকআপ পাঠানো অত্যন্ত সময়সাপেক্ষ ছিল।',
		'challenge_en'=> 'Manually transcoding multiple exports, extracting social poster frames, and syncing large asset folders after every video edit consumed hours.',
		'solution_bn' => 'একটি কমান্ড-লাইন স্ক্রিপ্ট যা এক কমান্ডে ফোল্ডারের সব ভিডিওকে কোয়ালিটি লস ছাড়া H.264/H.265 এ কনভার্ট করে ও ব্যাকআপ ড্রাইভে পাঠিয়ে দেয়।',
		'solution_en' => 'Engineered a Python CLI utility wrapping FFmpeg multi-threading to transcode videos in parallel, capture frame grabs, and trigger rsync/cloud sync.',
		'highlights_bn'=> array(
			'ভিজুয়াল লস ছাড়া ৬০% পর্যন্ত ফাইল সাইজ কমানো (CRF ব্যালেন্স)',
			'স্বয়ংক্রিয়ভাবে ভিডিওর পিক-মোমেন্ট থেকে থাম্বনেইল জেনারেশন',
			'মাল্টি-থ্রেডিং সাপোর্ট যা ব্যাচ রেন্ডার সময় অর্ধেক করে',
			'টার্মিনালে ক্লিন প্রগ্রেস বার ও নোটিফিকেশন অ্যালার্ট'
		),
		'highlights_en'=> array(
			'Up to 60% file compression with zero perceptible loss using CRF 22',
			'Automated keyframe detection for high-contrast social poster grabs',
			'Multi-threaded CPU/GPU acceleration cutting batch runtime in half',
			'Clean CLI progress telemetry and desktop system alerts upon completion'
		),
		'tags'        => array( 'Python', 'FFmpeg', 'Bash Script', 'CLI', 'Automation' ),
		'accent'      => '#00cec9',
		'icon'        => 'terminal',
		'image'       => '',
		'action_type' => 'code',
		'action_bn'   => 'স্ক্রিপ্ট কোড দেখুন',
		'action_en'   => 'View Script on GitHub',
		'direct_url'  => 'https://github.com/raisulsohan',
	),
);
?>

<div class="rs-hero<?php echo $rs_hero ? ' rs-hero--image' : ''; ?>">
	<h1 class="rs-hero__title">
		<?php if ( $rs_hero ) : ?>
			<?php echo rs_render_hero_image_html( $rs_is_en ? 'Portfolio' : 'পোর্টফোলিও' ); ?>
		<?php else : ?>
			<span><?php echo esc_html( $rs_is_en ? 'Portfolio' : 'পোর্টফোলিও' ); ?></span>
		<?php endif; ?>
	</h1>
</div>

<main class="rs-wrap rs-portfolio-page" id="rs-content">

	<!-- Introduction & Core Bio -->
	<header class="rs-portfolio-hero">
		<div class="rs-portfolio-hero__badge">
			<?php echo esc_html( $rs_is_en ? 'Multidisciplinary Creative & Engineer' : 'ভিজ্যুয়াল ক্রিয়েটর ও সফটওয়্যার ডেভেলপার' ); ?>
		</div>
		<h2 class="rs-portfolio-hero__headline">
			<?php if ( $rs_is_en ) : ?>
				Visual Storytelling Meets <span class="rs-portfolio-gradient">Clean Code</span>
			<?php else : ?>
				ভিজ্যুয়াল স্টোরিটেলিং ও <span class="rs-portfolio-gradient">ক্লিন কোডের সমন্বয়</span>
			<?php endif; ?>
		</h2>
		<p class="rs-portfolio-hero__bio">
			<?php if ( $rs_is_en ) : ?>
				I craft engaging visual narratives through video editing and 2D motion animation, while engineering lightweight, responsive websites and productivity tools (browser extensions, WordPress plugins, and automation scripts). Passionate about seamless storytelling, clean code, and zero-bloat digital experiences.
			<?php else : ?>
				আমি ভিডিও এডিটিং ও ২ডি মোশন অ্যানিমেশনের মাধ্যমে ভিজ্যুয়াল গল্প বলি, আবার একই সাথে আধুনিক ওয়েব প্রযুক্তি, ব্রাউজার এক্সটেনশন ও অটোমেশন স্ক্রিপ্ট তৈরি করে ডিজিটাল সমস্যার সমাধান করি। প্রতিটি কাট ও প্রতিটি লাইন কোডে পারফেকশন, স্পিড এবং মিনিমালিজম বজায় রাখাই আমার লক্ষ্য।
			<?php endif; ?>
		</p>

		<!-- Skill Highlights Strip -->
		<div class="rs-portfolio-skills">
			<div class="rs-portfolio-skill-pill">
				<span class="rs-portfolio-skill-pill__icon">💻</span>
				<strong><?php echo esc_html( $rs_is_en ? 'Web Development' : 'ওয়েব ডেভেলপমেন্ট' ); ?></strong>
				<span class="rs-portfolio-skill-pill__sub">WordPress • PHP • Vanilla JS • CSS3</span>
			</div>
			<div class="rs-portfolio-skill-pill">
				<span class="rs-portfolio-skill-pill__icon">🎬</span>
				<strong><?php echo esc_html( $rs_is_en ? 'Video & Animation' : 'ভিডিও ও মোশন' ); ?></strong>
				<span class="rs-portfolio-skill-pill__sub">Premiere Pro • After Effects • DaVinci</span>
			</div>
			<div class="rs-portfolio-skill-pill">
				<span class="rs-portfolio-skill-pill__icon">⚡</span>
				<strong><?php echo esc_html( $rs_is_en ? 'Extensions & Tools' : 'এক্সটেনশন ও টুলস' ); ?></strong>
				<span class="rs-portfolio-skill-pill__sub">Chrome MV3 • Plugins • Python Scripts</span>
			</div>
		</div>
	</header>

	<!-- Filter Controls -->
	<section class="rs-portfolio-filter-section" aria-label="<?php echo esc_attr( $rs_is_en ? 'Project Filter' : 'প্রজেক্ট ফিল্টার' ); ?>">
		<div class="rs-portfolio-filter" role="tablist">
			<button type="button" class="rs-portfolio-filter__btn is-active" data-filter="all" role="tab" aria-selected="true">
				<?php echo esc_html( $rs_is_en ? 'All Work' : 'সব কাজ' ); ?>
				<span class="rs-portfolio-filter__count"><?php echo count( $projects ); ?></span>
			</button>
			<button type="button" class="rs-portfolio-filter__btn" data-filter="web" role="tab" aria-selected="false">
				💻 <?php echo esc_html( $rs_is_en ? 'Web Development' : 'ওয়েব ডেভেলপমেন্ট' ); ?>
			</button>
			<button type="button" class="rs-portfolio-filter__btn" data-filter="video" role="tab" aria-selected="false">
				🎬 <?php echo esc_html( $rs_is_en ? 'Video & Animation' : 'ভিডিও ও অ্যানিমেশন' ); ?>
			</button>
			<button type="button" class="rs-portfolio-filter__btn" data-filter="tools" role="tab" aria-selected="false">
				⚡ <?php echo esc_html( $rs_is_en ? 'Extensions & Scripts' : 'এক্সটেনশন ও স্ক্রিপ্ট' ); ?>
			</button>
		</div>
	</section>

	<!-- Projects Grid -->
	<section class="rs-portfolio-grid" id="rs-portfolio-grid">
		<?php foreach ( $projects as $p ) : ?>
			<?php
			$card_domain = ! empty( $p['direct_url'] ) ? preg_replace( '#^https?://([^/]+).*$#', '$1', $p['direct_url'] ) : 'demo';
			?>
			<article class="rs-portfolio-card" data-category="<?php echo esc_attr( $p['category'] ); ?>" data-project-id="<?php echo esc_attr( $p['id'] ); ?>" id="project-<?php echo esc_attr( $p['id'] ); ?>">
				
				<!-- Card Visual Mockup / Real Screenshot -->
				<div class="rs-portfolio-card__visual rs-portfolio-card__visual--<?php echo esc_attr( $p['category'] ); ?> rs-open-case-study" data-project-id="<?php echo esc_attr( $p['id'] ); ?>" title="<?php echo esc_attr( $rs_is_en ? 'Open Case Study' : 'কেস স্টাডি দেখুন' ); ?>" role="button" tabindex="0">
					
					<?php if ( ! empty( $p['image'] ) ) : ?>
						<?php if ( 'web' === $p['category'] ) : ?>
							<div class="rs-portfolio-card__browser-bar">
								<span class="rs-portfolio-dot"></span>
								<span class="rs-portfolio-dot"></span>
								<span class="rs-portfolio-dot"></span>
								<span class="rs-portfolio-card__url"><?php echo esc_html( $card_domain ); ?></span>
							</div>
							<div class="rs-portfolio-card__img-wrap <?php echo ( ! empty( $p['image_fit'] ) && 'contain' === $p['image_fit'] ) ? 'is-contain' : ''; ?>">
								<img src="<?php echo esc_url( $p['image'] ); ?>" alt="<?php echo esc_attr( $rs_is_en ? $p['title_en'] : $p['title_bn'] ); ?>" class="rs-portfolio-card__img" loading="lazy">
							</div>
						<?php elseif ( 'video' === $p['category'] ) : ?>
							<div class="rs-portfolio-card__img-wrap" style="background-image: linear-gradient(rgba(0,0,0,0.25), rgba(0,0,0,0.45)), url('<?php echo esc_url( $p['image'] ); ?>'); background-size: cover; background-position: center;">
								<div class="rs-portfolio-card__play-btn">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 4 20 12 6 20 6 4"></polygon></svg>
								</div>
								<span class="rs-portfolio-card__media-tag"><?php echo esc_html( $rs_is_en ? $p['type_en'] : $p['type_bn'] ); ?></span>
							</div>
						<?php else : ?>
							<div class="rs-portfolio-card__img-wrap">
								<img src="<?php echo esc_url( $p['image'] ); ?>" alt="<?php echo esc_attr( $rs_is_en ? $p['title_en'] : $p['title_bn'] ); ?>" class="rs-portfolio-card__img" loading="lazy">
								<span class="rs-portfolio-card__media-tag"><?php echo esc_html( $rs_is_en ? $p['type_en'] : $p['type_bn'] ); ?></span>
							</div>
						<?php endif; ?>
					<?php else : ?>
						<!-- Fallback CSS Mockup -->
						<?php if ( 'video' === $p['category'] ) : ?>
							<div class="rs-portfolio-card__media-placeholder">
								<div class="rs-portfolio-card__play-btn">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 4 20 12 6 20 6 4"></polygon></svg>
								</div>
								<span class="rs-portfolio-card__media-tag"><?php echo esc_html( $rs_is_en ? $p['type_en'] : $p['type_bn'] ); ?></span>
							</div>
						<?php elseif ( 'web' === $p['category'] ) : ?>
							<div class="rs-portfolio-card__browser-bar">
								<span class="rs-portfolio-dot"></span>
								<span class="rs-portfolio-dot"></span>
								<span class="rs-portfolio-dot"></span>
								<span class="rs-portfolio-card__url"><?php echo esc_html( $card_domain ); ?></span>
							</div>
							<div class="rs-portfolio-card__web-preview">
								<div class="rs-portfolio-card__wire-block"></div>
								<div class="rs-portfolio-card__wire-line"></div>
								<div class="rs-portfolio-card__wire-line short"></div>
							</div>
						<?php else : ?>
							<div class="rs-portfolio-card__tool-banner">
								<div class="rs-portfolio-card__tool-icon">
									<?php if ( 'extension' === $p['icon'] ) : ?>
										🧩
									<?php elseif ( 'terminal' === $p['icon'] ) : ?>
										⌨️
									<?php else : ?>
										⚙️
									<?php endif; ?>
								</div>
								<span class="rs-portfolio-card__media-tag"><?php echo esc_html( $rs_is_en ? $p['type_en'] : $p['type_bn'] ); ?></span>
							</div>
						<?php endif; ?>
					<?php endif; ?>

					<div class="rs-portfolio-card__visual-hover">
						<span>📋 <?php echo esc_html( $rs_is_en ? 'Read Case Study' : 'কেস স্টাডি পড়ুন' ); ?></span>
					</div>
				</div>

				<!-- Card Content -->
				<div class="rs-portfolio-card__content">
					<div class="rs-portfolio-card__meta">
						<span class="rs-portfolio-card__badge"><?php echo esc_html( $rs_is_en ? $p['badge_en'] : $p['badge_bn'] ); ?></span>
						<span class="rs-portfolio-card__type"><?php echo esc_html( $rs_is_en ? $p['type_en'] : $p['type_bn'] ); ?></span>
					</div>

					<h3 class="rs-portfolio-card__title rs-open-case-study" data-project-id="<?php echo esc_attr( $p['id'] ); ?>" role="button" tabindex="0">
						<?php echo esc_html( $rs_is_en ? $p['title_en'] : $p['title_bn'] ); ?>
					</h3>

					<p class="rs-portfolio-card__desc">
						<?php echo esc_html( $rs_is_en ? $p['summary_en'] : $p['summary_bn'] ); ?>
					</p>

					<!-- Tags -->
					<div class="rs-portfolio-card__tags">
						<?php foreach ( $p['tags'] as $tag ) : ?>
							<span class="rs-portfolio-tag"><?php echo esc_html( $tag ); ?></span>
						<?php endforeach; ?>
					</div>

					<!-- Dual Action Footer: Case Study Trigger + Direct Outbound Preview -->
					<div class="rs-portfolio-card__footer">
						<button type="button" class="rs-portfolio-btn rs-portfolio-btn--details rs-open-case-study" data-project-id="<?php echo esc_attr( $p['id'] ); ?>" aria-label="<?php echo esc_attr( ( $rs_is_en ? 'Case study for ' : 'কেস স্টাডি: ' ) . ( $rs_is_en ? $p['title_en'] : $p['title_bn'] ) ); ?>">
							<span>📋 <?php echo esc_html( $rs_is_en ? 'Case Study' : 'কেস স্টাডি' ); ?></span>
						</button>

						<a href="<?php echo esc_url( $p['direct_url'] ); ?>" class="rs-portfolio-btn rs-portfolio-btn--direct" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( $rs_is_en ? 'Open original project destination' : 'মূল প্রজেক্টের ঠিকানায় যান' ); ?>">
							<span><?php echo esc_html( $rs_is_en ? $p['action_en'] : $p['action_bn'] ); ?></span>
							<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
								<line x1="7" y1="17" x2="17" y2="7"></line>
								<polyline points="7 7 17 7 17 17"></polyline>
							</svg>
						</a>
					</div>
				</div>

			</article>
		<?php endforeach; ?>
	</section>

	<!-- Empty state if no projects match filter -->
	<div class="rs-portfolio-empty" id="rs-portfolio-empty" style="display: none;">
		<p><?php echo esc_html( $rs_is_en ? 'No projects found in this category.' : 'এই ক্যাটাগরিতে কোনো প্রজেক্ট পাওয়া যায়নি।' ); ?></p>
	</div>

	<!-- Contact / Collaboration Section -->
	<footer class="rs-portfolio-cta">
		<h3 class="rs-portfolio-cta__title">
			<?php echo esc_html( $rs_is_en ? 'Have an interesting project in mind?' : 'কোনো নতুন কাজের পরিকল্পনা আছে?' ); ?>
		</h3>
		<p class="rs-portfolio-cta__desc">
			<?php if ( $rs_is_en ) : ?>
				Whether you need cinematic video editing, animated graphics, a custom website, or custom automation tools — feel free to connect!
			<?php else : ?>
				সিনেমাটিক ভিডিও এডিটিং, মোশন অ্যানিমেশন, দ্রুতগতির কাস্টম ওয়েবসাইট বা স্পেশাল কোনো এক্সটেনশন/স্ক্রিপ্ট বানাতে চাইলে যোগাযোগ করতে পারেন।
			<?php endif; ?>
		</p>
		<div class="rs-portfolio-cta__actions">
			<?php $rs_email = rs_option( 'rs_email' ); ?>
			<?php if ( $rs_email ) : ?>
				<a href="mailto:<?php echo esc_attr( $rs_email ); ?>" class="rs-portfolio-btn rs-portfolio-btn--primary">
					✉️ <?php echo esc_html( $rs_is_en ? 'Send an Email' : 'ইমেইল পাঠান' ); ?>
				</a>
			<?php endif; ?>
			<?php if ( rs_option( 'rs_linkedin' ) ) : ?>
				<a href="<?php echo esc_url( rs_option( 'rs_linkedin' ) ); ?>" target="_blank" rel="noopener noreferrer" class="rs-portfolio-btn rs-portfolio-btn--secondary">
					💼 LinkedIn
				</a>
			<?php endif; ?>
			<?php if ( rs_option( 'rs_facebook' ) ) : ?>
				<a href="<?php echo esc_url( rs_option( 'rs_facebook' ) ); ?>" target="_blank" rel="noopener noreferrer" class="rs-portfolio-btn rs-portfolio-btn--secondary">
					📘 Facebook
				</a>
			<?php endif; ?>
		</div>
	</footer>

</main>

<!-- =========================================================================
     Case Study Pop-up Modal (Native .rs-overlay Architecture)
     ====================================================================== -->
<div class="rs-overlay" id="rs-case-study-overlay" role="dialog" aria-modal="true" aria-labelledby="rs-modal-project-title" hidden>
	<div class="rs-modal rs-modal--case-study">
		<button class="rs-modal__close" type="button" id="rs-case-study-close" aria-label="<?php echo esc_attr( $rs_is_en ? 'Close' : 'বন্ধ করুন' ); ?>">
			<?php echo wp_kses( rs_icon( 'close', 18 ), rs_svg_tags() ); ?>
		</button>
		
		<div class="rs-modal__scroll" id="rs-case-study-scroll">
			<article class="rs-case-study-article">
				
				<!-- Modal Header -->
				<header class="rs-case-study-header">
					<div class="rs-case-study-meta">
						<span class="rs-case-study-badge" id="rs-modal-badge"></span>
						<span class="rs-case-study-type" id="rs-modal-type"></span>
					</div>
					<h2 class="rs-case-study-title" id="rs-modal-project-title"></h2>
					<div class="rs-case-study-subline" id="rs-modal-subline">
						<span class="rs-case-study-role" id="rs-modal-role"></span>
						<span class="rs-case-study-sep">•</span>
						<span class="rs-case-study-context" id="rs-modal-context"></span>
					</div>
				</header>

				<!-- Visual Preview Banner inside Modal -->
				<div class="rs-case-study-visual" id="rs-modal-visual">
					<div class="rs-case-study-visual__inner" id="rs-modal-visual-content"></div>
				</div>

				<!-- Case Study Sections -->
				<div class="rs-case-study-body">
					
					<!-- Overview / Summary -->
					<div class="rs-case-study-section">
						<p class="rs-case-study-lead" id="rs-modal-summary"></p>
					</div>

					<!-- The Challenge -->
					<div class="rs-case-study-section rs-case-study-box rs-case-study-box--challenge">
						<h4 class="rs-case-study-heading">
							🎯 <?php echo esc_html( $rs_is_en ? 'The Challenge & Context' : 'চ্যালেঞ্জ ও প্রেক্ষাপট' ); ?>
						</h4>
						<p id="rs-modal-challenge"></p>
					</div>

					<!-- The Solution & Process -->
					<div class="rs-case-study-section rs-case-study-box rs-case-study-box--solution">
						<h4 class="rs-case-study-heading">
							💡 <?php echo esc_html( $rs_is_en ? 'The Solution & Creative Process' : 'সমাধান ও কর্মপ্রক্রিয়া' ); ?>
						</h4>
						<p id="rs-modal-solution"></p>
					</div>

					<!-- Key Highlights -->
					<div class="rs-case-study-section">
						<h4 class="rs-case-study-heading">
							⭐ <?php echo esc_html( $rs_is_en ? 'Key Highlights & Results' : 'মূল ফলাফল ও বিশেষ অর্জন' ); ?>
						</h4>
						<ul class="rs-case-study-list" id="rs-modal-highlights"></ul>
					</div>

					<!-- Tools & Technologies -->
					<div class="rs-case-study-section">
						<h4 class="rs-case-study-heading">
							🛠 <?php echo esc_html( $rs_is_en ? 'Tools & Technologies Used' : 'ব্যবহৃত সফটওয়্যার ও টুলস' ); ?>
						</h4>
						<div class="rs-portfolio-card__tags" id="rs-modal-tags"></div>
					</div>

				</div>

				<!-- Modal Action Footer -->
				<footer class="rs-case-study-footer">
					<a href="#" class="rs-portfolio-btn rs-portfolio-btn--primary" id="rs-modal-action-btn" target="_blank" rel="noopener noreferrer">
						<span id="rs-modal-action-label"></span>
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
							<line x1="7" y1="17" x2="17" y2="7"></line>
							<polyline points="7 7 17 7 17 17"></polyline>
						</svg>
					</a>
					<a href="#" class="rs-portfolio-btn rs-portfolio-btn--secondary" id="rs-modal-github-btn" target="_blank" rel="noopener noreferrer" style="display: none;">
						<span>GitHub</span>
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
							<line x1="7" y1="17" x2="17" y2="7"></line>
							<polyline points="7 7 17 7 17 17"></polyline>
						</svg>
					</a>
					<button type="button" class="rs-portfolio-btn rs-portfolio-btn--secondary" id="rs-modal-dismiss-btn">
						<?php echo esc_html( $rs_is_en ? 'Close Case Study' : 'বন্ধ করুন' ); ?>
					</button>
				</footer>

			</article>
		</div>
	</div>
</div>

<!-- Structured Projects Data for Client-Side Modal -->
<script id="rs-portfolio-data" type="application/json">
<?php
$client_data = array();
foreach ( $projects as $p ) {
	$client_data[ $p['id'] ] = array(
		'id'          => $p['id'],
		'category'    => $p['category'],
		'type'        => $rs_is_en ? $p['type_en'] : $p['type_bn'],
		'badge'       => $rs_is_en ? $p['badge_en'] : $p['badge_bn'],
		'title'       => $rs_is_en ? $p['title_en'] : $p['title_bn'],
		'summary'     => $rs_is_en ? $p['summary_en'] : $p['summary_bn'],
		'role'        => ( $rs_is_en ? 'Role: ' : 'ভূমিকা: ' ) . ( $rs_is_en ? $p['role_en'] : $p['role_bn'] ),
		'context'     => $rs_is_en ? $p['context_en'] : $p['context_bn'],
		'challenge'   => $rs_is_en ? $p['challenge_en'] : $p['challenge_bn'],
		'solution'    => $rs_is_en ? $p['solution_en'] : $p['solution_bn'],
		'highlights'  => $rs_is_en ? $p['highlights_en'] : $p['highlights_bn'],
		'tags'        => $p['tags'],
		'accent'      => $p['accent'],
		'icon'        => $p['icon'],
		'image'       => ! empty( $p['image'] ) ? $p['image'] : '',
		'image_fit'   => ! empty( $p['image_fit'] ) ? $p['image_fit'] : 'cover',
		'action_type' => $p['action_type'],
		'action_label'=> $rs_is_en ? $p['action_en'] : $p['action_bn'],
		'direct_url'  => $p['direct_url'],
		'github_url'  => ! empty( $p['github_url'] ) ? $p['github_url'] : '',
	);
}
echo wp_json_encode( $client_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
?>
</script>

<script>
(function() {
	'use strict';

	/* 1. Category Filter Logic */
	var buttons = document.querySelectorAll('.rs-portfolio-filter__btn');
	var cards   = document.querySelectorAll('.rs-portfolio-card');
	var empty   = document.getElementById('rs-portfolio-empty');

	if (buttons.length && cards.length) {
		buttons.forEach(function(btn) {
			btn.addEventListener('click', function() {
				var filter = this.getAttribute('data-filter');

				buttons.forEach(function(b) {
					b.classList.remove('is-active');
					b.setAttribute('aria-selected', 'false');
				});
				this.classList.add('is-active');
				this.setAttribute('aria-selected', 'true');

				var visibleCount = 0;
				cards.forEach(function(card) {
					var cat = card.getAttribute('data-category');
					if (filter === 'all' || filter === cat) {
						card.style.display = '';
						visibleCount++;
					} else {
						card.style.display = 'none';
					}
				});

				if (empty) {
					empty.style.display = visibleCount === 0 ? 'block' : 'none';
				}
			});
		});
	}

	/* 2. Case Study Pop-up Modal Logic */
	var rawData = document.getElementById('rs-portfolio-data');
	if (!rawData) return;

	var projectsMap = {};
	try {
		projectsMap = JSON.parse(rawData.textContent || '{}');
	} catch (e) {
		console.error('Failed to parse portfolio data', e);
	}

	var overlay      = document.getElementById('rs-case-study-overlay');
	var scrollArea   = document.getElementById('rs-case-study-scroll');
	var closeBtn     = document.getElementById('rs-case-study-close');
	var dismissBtn   = document.getElementById('rs-modal-dismiss-btn');
	var titleEl      = document.getElementById('rs-modal-project-title');
	var badgeEl      = document.getElementById('rs-modal-badge');
	var typeEl       = document.getElementById('rs-modal-type');
	var roleEl       = document.getElementById('rs-modal-role');
	var contextEl    = document.getElementById('rs-modal-context');
	var summaryEl    = document.getElementById('rs-modal-summary');
	var challengeEl  = document.getElementById('rs-modal-challenge');
	var solutionEl   = document.getElementById('rs-modal-solution');
	var highlightsEl = document.getElementById('rs-modal-highlights');
	var tagsEl       = document.getElementById('rs-modal-tags');
	var visualEl     = document.getElementById('rs-modal-visual-content');
	var actionBtn    = document.getElementById('rs-modal-action-btn');
	var actionLabel  = document.getElementById('rs-modal-action-label');
	var githubBtn    = document.getElementById('rs-modal-github-btn');

	function openCaseStudy(projectId) {
		var p = projectsMap[projectId];
		if (!p || !overlay) return;

		titleEl.textContent    = p.title;
		badgeEl.textContent    = p.badge;
		typeEl.textContent     = p.type;
		roleEl.textContent     = p.role;
		contextEl.textContent  = p.context;
		summaryEl.textContent  = p.summary;
		challengeEl.textContent= p.challenge;
		solutionEl.textContent = p.solution;

		// Render Highlights List
		highlightsEl.innerHTML = '';
		if (Array.isArray(p.highlights)) {
			p.highlights.forEach(function(h) {
				var li = document.createElement('li');
				li.textContent = h;
				highlightsEl.appendChild(li);
			});
		}

		// Render Tags
		tagsEl.innerHTML = '';
		if (Array.isArray(p.tags)) {
			p.tags.forEach(function(tag) {
				var sp = document.createElement('span');
				sp.className = 'rs-portfolio-tag';
				sp.textContent = tag;
				tagsEl.appendChild(sp);
			});
		}

		// Action Buttons
		actionBtn.href = p.direct_url;
		actionLabel.textContent = p.action_label;

		if (p.github_url && githubBtn) {
			githubBtn.href = p.github_url;
			githubBtn.style.display = 'inline-flex';
			var isEn = document.documentElement.lang.indexOf('en') === 0;
			githubBtn.querySelector('span').textContent = isEn ? 'View on GitHub' : 'গিটহাবে কোড দেখুন';
		} else if (githubBtn) {
			githubBtn.style.display = 'none';
		}

		// Dynamic Visual Banner (Real Screenshot or CSS Mockup)
		var displayDomain = p.direct_url.replace(/^https?:\/\//, '').replace(/\/.*$/, '');
		if (p.image) {
			if (p.category === 'web') {
				var fitClass = p.image_fit === 'contain' ? ' is-contain' : '';
				visualEl.innerHTML = '<div class="rs-case-study-web-mockup"><div class="rs-portfolio-card__browser-bar"><span class="rs-portfolio-dot"></span><span class="rs-portfolio-dot"></span><span class="rs-portfolio-dot"></span><span class="rs-portfolio-card__url">' + displayDomain + '</span></div><div class="rs-case-study-img-wrap' + fitClass + '"><img src="' + p.image + '" alt="' + p.title + '" class="rs-case-study-img"></div></div>';
			} else if (p.category === 'video') {
				visualEl.innerHTML = '<div class="rs-case-study-video-mockup" style="background-image: linear-gradient(rgba(0,0,0,0.25), rgba(0,0,0,0.45)), url(' + p.image + '); background-size: cover; background-position: center;"><div class="rs-portfolio-card__play-btn"><svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 4 20 12 6 20 6 4"></polygon></svg></div><span class="rs-case-study-video-label">' + (document.documentElement.lang.indexOf('en') === 0 ? 'HD Video Preview' : 'এইচডি ভিডিও প্রিভিউ') + '</span></div>';
			} else {
				visualEl.innerHTML = '<div class="rs-case-study-img-wrap"><img src="' + p.image + '" alt="' + p.title + '" class="rs-case-study-img"></div>';
			}
		} else {
			if (p.category === 'video') {
				visualEl.innerHTML = '<div class="rs-case-study-video-mockup"><div class="rs-portfolio-card__play-btn"><svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 4 20 12 6 20 6 4"></polygon></svg></div><span class="rs-case-study-video-label">' + (document.documentElement.lang.indexOf('en') === 0 ? 'HD Video Preview' : 'এইচডি ভিডিও প্রিভিউ') + '</span></div>';
			} else if (p.category === 'web') {
				visualEl.innerHTML = '<div class="rs-case-study-web-mockup"><div class="rs-portfolio-card__browser-bar"><span class="rs-portfolio-dot"></span><span class="rs-portfolio-dot"></span><span class="rs-portfolio-dot"></span><span class="rs-portfolio-card__url">' + displayDomain + '</span></div><div class="rs-case-study-web-body"><span>⚡ ' + (document.documentElement.lang.indexOf('en') === 0 ? 'Fast Responsive Zero-Plugin Web Platform' : 'দ্রুতগতির জিরো-প্লাগিন রেসপনসিভ ওয়েবসাইট') + '</span></div></div>';
			} else {
				visualEl.innerHTML = '<div class="rs-case-study-tool-mockup"><div class="rs-case-study-tool-badge">' + (p.icon === 'extension' ? '🧩' : (p.icon === 'terminal' ? '⌨️' : '⚙️')) + '</div><span>' + p.type + '</span></div>';
			}
		}

		// Show Modal
		overlay.hidden = false;
		document.body.style.overflow = 'hidden';
		if (scrollArea) scrollArea.scrollTop = 0;
	}

	function closeCaseStudy() {
		if (!overlay || overlay.hidden) return;
		overlay.hidden = true;
		document.body.style.overflow = '';
	}

	// Attach click handlers to triggers
	document.querySelectorAll('.rs-open-case-study').forEach(function(trigger) {
		trigger.addEventListener('click', function(e) {
			e.preventDefault();
			var pid = this.getAttribute('data-project-id');
			if (pid) {
				openCaseStudy(pid);
			}
		});
		trigger.addEventListener('keydown', function(e) {
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				var pid = this.getAttribute('data-project-id');
				if (pid) openCaseStudy(pid);
			}
		});
	});

	// Close buttons
	if (closeBtn) closeBtn.addEventListener('click', closeCaseStudy);
	if (dismissBtn) dismissBtn.addEventListener('click', closeCaseStudy);

	// Backdrop click closes
	if (overlay) {
		overlay.addEventListener('mousedown', function(e) {
			if (e.target === overlay) {
				closeCaseStudy();
			}
		});
	}

	// ESC key closes
	document.addEventListener('keydown', function(e) {
		if ((e.key === 'Escape' || e.key === 'Esc') && overlay && !overlay.hidden) {
			e.preventDefault();
			closeCaseStudy();
		}
	});

})();
</script>

<?php
get_footer();
