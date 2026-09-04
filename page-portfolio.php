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
		'challenge_bn'=> "অনলাইন বিজ্ঞান ও প্রযুক্তি ম্যাগাজিনগুলোতে সাধারণত হিরো গ্রিড, এজেক্স সার্চ, ভিডিও পপ-আপ, এসইও মেটা এবং বুকমার্কের মতো ফিচারগুলোর জন্য অন্তত ২০-৩০টি প্লাগিন ব্যবহার করতে হয়। এতে প্রতিটি পেজে বিপুল পরিমাণ অপ্রয়োজনীয় CSS/JS স্ক্রিপ্ট জমে সাইট ভারী হয়ে যায়, ডাটাবেস কোয়েরি বাড়ে এবং মোবাইল ইন্টারনেটে পাঠকদের জন্য সাইট লোড হতে প্রচুর সময় লাগে।\n\nআমাদের মূল চ্যালেঞ্জ ছিল: কোনো রেডিমেড পেজবিল্ডার (Elementor/Divi), কোনো থার্ড-পার্টি এসইও প্লাগিন (Yoast/RankMath) এবং এমনকি কোনো jQuery লাইব্রেরি ছাড়াই শুধুমাত্র ওয়ার্ডপ্রেসের কোর পিএইচপি এপিআই এবং আধুনিক ভ্যানিলা ES6+ জাভাস্ক্রিপ্ট ব্যবহার করে একটি অত্যন্ত আকর্ষণীয়, ফ্লুইড ও জটিল প্রকাশনা সাইট তৈরি করা।",
		'challenge_en'=> "Digital magazines traditionally rely on 20 to 30 external plugins to support features like hero mosaics, live search, video popups, SEO meta, and reading bookmarks. This introduces severe database overhead, render-blocking scripts, and sluggish performance on mobile connections.\n\nOur architectural goal was to eliminate third-party dependencies entirely: building a modern, feature-dense science publication without page builders (Elementor/Divi), without SEO plugins (Yoast/RankMath), and without jQuery—relying strictly on native WordPress PHP APIs and clean Vanilla ES6+ JavaScript.",
		'solution_bn' => "১. ফিগমা স্টাইল ৮-কার্ড হিরো মোজাইক: জটিল পেজবিল্ডার বাদ দিয়ে পিওর আধুনিক CSS Grid ও Flexbox আর্কিটেকচারে একটি রেসপনসিভ ৮-কার্ড মোজাইক তৈরি করা হয়েছে, যা বড় ডেস্কটপ থেকে শুরু করে মোবাইলেও স্বয়ংক্রিয়ভাবে খাপ খেয়ে যায়।\n\n২. নেটিভ এজেক্স রিডিং মডাল: পাঠক কোনো লেখায় ক্লিক করলে পুরো পেজ রিলোড না হয়ে তাৎক্ষণিকভাবে ব্যাকগ্রাউন্ডে কনটেন্ট ফেচ করে একটি সিনেমাটিক রিডিং মডাল ওপেন হয়, যাতে পড়ার অভিজ্ঞতা কোনো বাধা ছাড়াই অব্যাহত থাকে।\n\n৩. ডায়নামিক রেশিও সিনেমেটিক ভিডিও প্লেয়ার: ইউটিউব ভিডিও দেখানোর জন্য ৪টি ভিন্ন অ্যাসপেক্ট রেশিও (১৬:৯, ৯:১৬, ৪:৫, ১:১) সাপোর্ট সহ একটি লাইটওয়েট নেটিভ পপ-আপ প্লেয়ার তৈরি করা হয়েছে।\n\n৪. বিল্ট-ইন এসইও ইঞ্জিন ও লাইভ গুগল প্রিভিউ: ক্লাসিক এডিটরে একটি কাস্টম মেটাবক্স ডিজাইন করা হয়েছে যা গুগল সার্চের মতো রিয়েল-টাইম ডেস্কটপ ও মোবাইল প্রিভিউ দেখায়, মেটা ডেসক্রিপশন তৈরি করে এবং স্বয়ংক্রিয়ভাবে Article ও Organization-এর জন্য বৈধ JSON-LD Rich Snippet স্কিমা জেনারেট করে।\n\n৫. লোকালস্টোরেজ বুকমার্ক ড্রয়ার: সার্ভারে কোনো বাড়তি লোড না দিয়ে ব্রাউজারের নিজস্ব লোকালস্টোরেজে 'পরে পড়ার তালিকা' সংরক্ষণের জন্য স্লাইড-আউট ড্রয়ার ইন্টারফেস তৈরি করা হয়েছে।\n\n৬. গিটহাব সেলফ-আপডেটার ও লাইসেন্স ম্যানেজার: থিমের ভবিষ্যৎ আপডেটগুলো যেন সরাসরি ওয়ার্ডপ্রেস ড্যাশবোর্ডে ওয়ান-ক্লিকে পাওয়া যায়, সেজন্য গিটহাব কমিট এসএইচএ (Commit SHA) ভ্যালিডেশন ভিত্তিক নিজস্ব আপডেটার এবং ডোমেইন লাইসেন্সিং সিস্টেম যুক্ত করা হয়েছে।",
		'solution_en' => "1. Figma-Grade 8-Card Hero Mosaic: Engineered an adaptive 8-post visual grid using pure CSS Grid and Flexbox, eliminating bulky page builders while maintaining responsive fluid scaling across all viewports.\n\n2. Native AJAX Reading Modal: Clicking any article fetches content via background AJAX and presents a distraction-free cinematic reading modal with zero page reloads.\n\n3. Dynamic Aspect-Ratio Video Engine: Custom lightweight popup player supporting 4 dynamic YouTube aspect ratios (16:9, 9:16, 4:5, 1:1) with responsive sizing.\n\n4. Built-in SEO Suite with Live Google Preview: Custom classic editor metabox rendering real-time mobile/desktop Google snippet previews, automated meta generation, and valid JSON-LD Article & Organization structured data.\n\n5. LocalStorage Reading Bookmarks Drawer: Zero server database overhead; articles are saved directly to the reader's device storage with a sleek slide-out drawer interface.\n\n6. Native GitHub Auto-Updater & License Manager: Delivers one-click updates directly inside the WordPress dashboard pinned to commit SHAs, integrated with a domain licensing validation screen.",
		'highlights_bn'=> array(
			'০টি এক্সটার্নাল প্লাগিন — এসইও, লাইভ সার্চ, বুকমার্ক, মডাল ও কাস্টমাইজার সম্পূর্ণ নেটিভ কোডে নির্মিত',
			'১০০% ভ্যানিলা ES6+ জাভাস্ক্রিপ্ট (কোনো jQuery নেই) ও আধুনিক CSS ভ্যারিয়েবল আর্কিটেকচার',
			'ফিগমা ও ম্যাগাজিন লেআউট: ৮-কার্ড হিরো মোজাইক এবং রেসপনসিভ মাল্টি-কলাম ক্যাটাগরি গ্রিড',
			'ইউটিউবের জন্য সিনেমেটিক ভিডিও পপ-আপ (১৬:৯, ৯:১৬, ৪:৫, ১:১ অ্যাসপেক্ট রেশিও সাপোর্ট)',
			'ওয়ার্ডপ্রেস ক্লাসিক এডিটরে লাইভ গুগল স্নিপেট প্রিভিউ সহ নিজস্ব এসইও মেটাবক্স ও JSON-LD স্কিমা',
			'নেটিভ গিটহাব অটো-আপডেটার — ড্যাশবোর্ড থেকেই এক ক্লিকে থিম আপডেট পাওয়ার সুবিধা',
			'কোর ওয়েব ভাইটালস ও WCAG অপ্টিমাইজড — মোবাইল ও ডেস্কটপ উভয়েই সাব-সেকেন্ড পেজ লোড ও ৯৫+ পারফরম্যান্স স্কোর'
		),
		'highlights_en'=> array(
			'Zero-Plugin Architecture: Native built-in SEO, live search, bookmarks, and modals without third-party plugins',
			'Pure Vanilla ES6+ JavaScript (zero jQuery) with modular CSS custom property architecture',
			'Figma Mac & Magazine layout: 8-card hero mosaic and responsive multi-column category grids',
			'Cinematic video popups supporting dynamic aspect ratios (16:9, 9:16, 4:5, 1:1) from YouTube',
			'In-editor Google search snippet preview, Open Graph cards, and native JSON-LD schema generator',
			'Native GitHub Auto-Updater delivering instant dashboard updates via commit SHA verification',
			'Core Web Vitals & WCAG optimized: sub-second load times and 95+ PageSpeed scores on mobile & desktop'
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
		'role_bn'     => 'ফুলস্ট্যাক থিম আর্কিটেক্ট ও লেখক',
		'role_en'     => 'Full-Stack Theme Architect & Writer',
		'context_bn'  => 'ব্যক্তিগত সাহিত্য ও ব্লগ পোর্টাল • ভার্সন ৭.৪',
		'context_en'  => 'Personal Literature & Blog Portal • v7.4',
		'challenge_bn'=> "সাহিত্যের জন্য একটি বিভ্রান্তিমুক্ত (distraction-free), পরিচ্ছন্ন এবং দীর্ঘ সময় পড়ার উপযোগী ব্যক্তিগত প্রকাশনা সাইট তৈরি করা যেখানে পাঠক স্বাচ্ছন্দ্যে হারিয়ে যেতে পারেন। সাধারণ ওয়ার্ডপ্রেস সাইটে পেজ বদলালে পুরো ব্রাউজার রিলোড হয়ে সাদা পর্দা ভেসে ওঠে, যা পড়ার একাগ্রতা ভেঙে দেয়।\n\nএকই সাথে দ্বিভাষিক মাল্টিসাইট নেটওয়ার্কে পারমালিংক কনফ্লিক্ট এড়ানো, কোনো ট্র্যাকিং স্ক্রিপ্ট বা ভারী ডাটাবেস কোয়েরি ছাড়াই পাঠকের পড়ার অগ্রগতি (Resume Reading) ও বুকমার্ক মনে রাখা এবং ব্যবহারকারীর পছন্দের যে কোনো রঙে সাইটের লেখার স্পষ্টতা (Contrast) নিশ্চিত করাই ছিল প্রধান কারিগরি চ্যালেঞ্জ।",
		'challenge_en'=> "Creating an immersive, distraction-free digital literature platform that honors long-form reading. Standard WordPress sites suffer from jarring full-page browser reloads that disrupt reading flow, while multisite bilingual installations often introduce permalink collisions and /blog prefix bloat.\n\nThe challenge was achieving seamless pushState AJAX navigation, robust client-side reading state preservation (Resume Reading & Bookmarks) with absolute user privacy, and mathematical color accessibility across any user-selected palette without external plugins.",
		'solution_bn' => "১. পুশ-স্টেট এজেক্স নেভিগেশন ও সিঙ্গেল ফলব্যাক: প্রতিটি পোস্টের লিংকে ক্লিক করলে ব্যাকগ্রাউন্ডে এজেক্স ফ্র্যাগমেন্ট এনে মডালে ওপেন হয় এবং ব্রাউজারের ইউআরএল বারে আসল পারমালিংক লাইভ আপডেট হয় (window.history.pushState)। পাঠক ব্যাক বাটনে চাপ দিলে মডাল বন্ধ হয়ে আগের তালিকায় ফিরে যায়। আবার সেই পোস্টের ইউআরএল সরাসরি খুললে single.php স্বয়ংক্রিয়ভাবে ফুল-পেজ অভিজ্ঞতায় একই রূপ নিয়ে লোড হয়।\n\n২. গাণিতিক কালার প্যালেট ও WCAG 4.5:1 অ্যাক্সেসিবিলিটি: পাঠক নিজের ইচ্ছেমতো যেকোনো অ্যাকসেন্ট কালার বেছে নিতে পারেন। একটি নিজস্ব অ্যালগরিদম সেই রঙের উজ্জ্বলতা ও স্যাচুরেশন গণনা করে স্বয়ংক্রিয়ভাবে ডার্ক/লাইট মোডের জন্য WCAG অনুমোদিত নিখুঁত ৪.৫:১ কনট্রাস্ট রেশিও তৈরি করে।\n\n৩. জিরো-ইমেজ প্রসিডিউরাল ৩ডি বুকশেল্ফ: শত শত বইয়ের কভার ইমেজ লোড করার ব্যান্ডউইথ খরচ কমাতে একটি পিওর সিএসএস বুকশেল্ফ তৈরি করা হয়েছে। বইয়ের জঁরা (Genre) থেকে হ্যাশ ফাংশন দিয়ে কালার প্যালেট এবং টাইটেলের দৈর্ঘ্য মেপে বইয়ের বাস্তবসম্মত উচ্চতা নির্ধারণ করে স্বয়ংক্রিয়ভাবে স্পাইন আঁকা হয়—এতে ০ কিলোবাইট এক্সটার্নাল ইমেজ খরচ হয়!\n\n৪. রিডিং প্রগ্রেস ও রিজ্যুম নোটিফিকেশন: পাঠক কতদূর পড়েছেন তা স্ক্রল প্রগ্রেস বার ও 'বাকি সময় (Time Left)' দিয়ে লাইভ ট্র্যাক হয়। মাঝপথে চলে গেলে পরবর্তীতে সাইটে এলে স্বয়ংক্রিয়ভাবে যেখান থেকে পড়া শেষ হয়েছিল সেখান থেকে শুরু করার স্মার্ট প্রম্পট দেওয়া হয়।\n\n৫. স্বয়ংসম্পূর্ণ এসইও ও স্মার্ট ৪০৪ রিকভারি: কোনো থার্ড-পার্টি এসইও প্লাগিন ছাড়াই স্বয়ংক্রিয় OpenGraph, Twitter Cards এবং BreadcrumbList সহ JSON-LD স্কিমা তৈরি হয়। কোনো লিংক ভেঙে গেলে স্মার্ট রিকভারি অ্যালগরিদম আর্টিকেলের স্লাগের বানান বিশ্লেষণ করে সবচেয়ে প্রাসঙ্গিক লেখার সন্ধান দেয়।\n\n৬. সেলফ-হোস্টেড বাংলা ফন্ট ও গিটহাব কমিট আপডেটার: কোনো এক্সটার্নাল গুগল ফন্ট রিকোয়েস্ট ছাড়া নিজস্ব সার্ভার থেকে WOFF2 ফরম্যাটে বাংলা ফন্ট পরিবেশন এবং ব্রাঞ্চ ক্যাশিং সমস্যা এড়াতে কমিট হ্যাশ দিয়ে ভ্যালিডেট করে স্বয়ংক্রিয় থিম আপডেট।",
		'solution_en' => "1. PushState AJAX Architecture & Fallback: Clicking an article opens an instant modal while dynamically synchronizing the browser URL (window.history.pushState). Browser back/forward buttons seamlessly restore navigation state, while direct visits gracefully render single.php with identical visual presentation.\n\n2. Algorithmic Color Engine with WCAG 4.5:1 Contrast: Allows users to choose any custom accent hue. A custom luminance and saturation calculator procedurally adjusts the entire theme palette to strictly guarantee WCAG AA 4.5:1 contrast compliance.\n\n3. Procedural CSS 3D Bookshelf (Zero Images): Renders hundreds of dynamic book spines purely in procedural CSS. Spines calculate HSL colors from genre hashes and dynamic vertical heights from title lengths, incurring 0 KB external image load.\n\n4. Reading Progress & Smart Resume Notification: Tracks scroll depth and dynamically recalculates reading time left. Remembers reading scroll positions locally to display an instant 'Resume Reading' toast on return.\n\n5. Self-Contained SEO & Fuzzy 404 Recovery: Native Open Graph, Twitter Cards, and BreadcrumbList JSON-LD structured data. A custom phoneme-based fuzzy slug analyzer intelligently redirects broken URLs to the closest published article.\n\n6. Self-Hosted Bengali WOFF2 Fonts & Commit-SHA Updater: Completely independent from external Google Fonts servers for optimal privacy and speed, paired with a GitHub self-updater pinned to immutable commit hashes.",
		'highlights_bn'=> array(
			'১০০/১০০ গুগল পেজস্পিড ও কোর ওয়েব ভাইটালস — কোনো ভারী ফ্রন্টএন্ড ফ্রেমওয়ার্কের বোঝা নেই',
			'০টি এক্সটার্নাল প্লাগিন — রিডিং মডাল, বুকমার্ক, ভিউ ট্র্যাকার, এসইও ও কাস্টমাইজার সম্পূর্ণ অন্তর্নির্মিত',
			'দ্বিভাষিক মাল্টিসাইট অপ্টিমাইজেশন — বাংলা (/) ও ইংরেজি (/en/) সাবসাইটের জন্য নিরবচ্ছিন্ন ল্যাঙ্গুয়েজ সুইচিং ও ক্লিন পারমালিংক',
			'স্মার্ট ভিউ ও রিডার কাউন্টার — ক্যাশ বাইপাস করে রিয়েল ইউনিক রিডার ট্র্যাক করতে নিজস্ব লাইটওয়েট REST এন্ডপয়েন্ট',
			'অটো ইমেজ অপ্টিমাইজেশন — আপলোড করা ছবি স্বয়ংক্রিয়ভাবে থিমের ভেতর থেকেই WebP ফরম্যাটে রূপান্তর ও রিসাইজ',
			'গিটহাব পিনড কমিট আপডেটার — ব্রাঞ্চ ক্যাশিং সমস্যা এড়াতে কমিট হ্যাশ দিয়ে ভ্যালিডেট করে স্বয়ংক্রিয় থিম আপডেট',
			'জিরো-ইমেজ সিএসএস ভার্চুয়াল বুকশেল্ফ — প্রসিডিউরাল অ্যালগরিদম ভিত্তিক অনন্য ইন্টারেক্টিভ ইন্টারফেস'
		),
		'highlights_en'=> array(
			'100/100 Google PageSpeed & Core Web Vitals across mobile and desktop devices',
			'Zero external plugins: reading modal, bookmarks, view tracking, SEO, and settings completely built-in',
			'Multisite bilingual optimization: clean permalinks and instant language switching between / and /en/',
			'Smart reader counter: lightweight native REST endpoint (POST /wp-json/rs/v1/view/<id>) bypassing cache',
			'Automated image conversion: auto-resizes and converts uploads to next-gen WebP without third-party tools',
			'GitHub Pinned-Commit Auto-Updater: reliable one-click dashboard updates pinned to immutable commit SHAs',
			'Procedural 3D CSS Bookshelf: procedural genre-to-HSL hash rendering with zero external graphics load'
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
		'id'          => 'thrivedesk-explainer',
		'category'    => 'video',
		'type_bn'     => 'সাস প্রডাক্ট এক্সপ্লেইনার ও মোশন অ্যানিমেশন',
		'type_en'     => 'SaaS Product Explainer & Motion Animation',
		'badge_bn'    => 'সাস অ্যানিমেশন • ৩ মিনিট',
		'badge_en'    => 'SaaS Explainer • 3 Mins',
		'title_bn'    => 'ThriveDesk Overview — ৩ মিনিটের সাস মোশন অ্যানিমেশন ও প্রোডাক্ট ওয়াকথ্রু',
		'title_en'    => 'ThriveDesk Overview — 3-Minute SaaS Motion Animation & Product Walkthrough',
		'summary_bn'  => 'গ্রোয়িং বিজনেসের কাস্টমার সাপোর্ট সহজ করতে থ্রাইভডেস্কের সেন্ট্রালাইজড ইনবক্স, টিকেট ডেলিগেশন, এআই রেসপন্স ড্রাফটিং, অটোমেশন রুলস, লাইভ চ্যাট অ্যাসিস্ট্যান্ট ও নলেজবেস ফিচারগুলোর আকর্ষণীয় ২ডি মোশন গ্রাফিক্স ওয়াকথ্রু।',
		'summary_en'  => 'Dynamic 2D SaaS motion explainer visualizing ThriveDesk\'s all-in-one customer support suite—shared inboxes, AI drafting, ticket delegation, automated workflows, live chat widgets, and self-service knowledge base in 3 minutes.',
		'role_bn'     => 'মোশন ডিজাইনার ও এক্সপ্লেইনার অ্যানিমেটর',
		'role_en'     => 'Motion Designer & Explainer Animator',
		'context_bn'  => 'অফিসিয়াল SaaS ওভারভিউ ভিডিও • ৩ মিনিট • ১৬:৯',
		'context_en'  => 'Official SaaS Overview Video • 3 Mins • 16:9',
		'challenge_bn'=> "থ্রাইভডেস্কের মতো একটি পরিপূর্ণ মাল্টি-ফাংশনাল কাস্টমার সাপোর্ট স্যুট—যাতে রয়েছে সেন্ট্রালাইজড ইনবক্স (Gmail, Outlook, Zoho), টিকেট ডেলিগেশন, ট্যাগ ও কালার কোডিং, ক্যানড রিপ্লাই, এআই ড্রাফটিং, প্রাইভেট নোটস ও টিম @মেনশন, ডুপ্লিকেট টিকেট মার্জিং, অটোমেশন রুলস ও স্প্যাম ফিল্টারিং, লাইভ চ্যাট অ্যাসিস্ট্যান্ট, রিয়েল-টাইম ভিজিটর ট্র্যাকিং, নলেজবেস পোর্টাল এবং ওয়ার্ডপ্রেস, WooCommerce ও Shopify-এর গভীর ইন্টিগ্রেশন—এই বিশাল জটিল প্ল্যাটফর্মকে মাত্র ৩ মিনিটের ভেতর সাধারণ ব্যবসা পরিচালকদের কাছে আকর্ষণীয় ও সাবলীলভাবে ফুটিয়ে তোলা ছিল মূল চ্যালেঞ্জ।\n\nসাধারণ স্ক্রিন রেকর্ডিং দিয়ে দেখালে ভিডিওটি একঘেয়ে ও ধীরগতির হয়ে পড়ে। তাই সফটওয়্যারটির প্রতিটি ইউআই এলিমেন্টকে স্ক্রিন থেকে আলাদা করে স্কেলেবল ভেক্টর গ্রাফিক্সে রূপান্তর করা, স্ন্যাপি ট্রানজিশনের মাধ্যমে জটিল ওয়ার্কফ্লোকে সহজ ভিজ্যুয়াল মেটাফোরে উপস্থাপন করা এবং প্রফেশনাল ভয়েসওভারের রিদমের সাথে নিখুঁত সিঙ্ক বজায় রাখা ছিল প্রধান কারিগরি ও ক্রিয়েটিভ চ্যালেঞ্জ।",
		'challenge_en'=> "ThriveDesk encompasses a comprehensive customer support ecosystem: unified email syncing (Gmail, Outlook, Zoho), team ticket delegation, color-coded tagging, canned replies, AI drafting, internal private notes, team @mentions, conversation history, ticket deduplication, automated routing & spam filtering, live website chat assistants, real-time visitor tracking, self-service knowledge bases, and deep e-commerce integrations (WordPress, WooCommerce, Shopify). Distilling this sprawling platform into a concise, engaging 3-minute visual walkthrough without overwhelming business owners was a profound communication challenge.\n\nTraditional desktop screencasts feel static, cluttered, and sluggish. The creative and technical challenge was deconstructing the product's UI into modular vector compositions, orchestrating fluid camera choreographies and snappy transition curves across 15+ feature states, and establishing rhythmic kinetic motion tightly locked to voiceover cadence.",
		'solution_bn' => "১. ৩-অ্যাক্ট ভিজ্যুয়াল আর্কিটেকচার: ভিডিওটিকে তিনটি স্পষ্ট অধ্যায়ে বিন্যস্ত করা হয়েছে— (ক) ইনবক্স সেটআপ, টিম অনবোর্ডিং ও টিকেট ডেলিগেশন; (খ) অটোমেশন রুলস, এআই ড্রাফটিং, ক্যানড রিপ্লাই ও ইন্টারনাল কোলাবোরেশন; এবং (গ) লাইভ চ্যাট অ্যাসিস্ট্যান্ট, রিয়েল-টাইম ভিজিটর এনগেজমেন্ট, নলেজবেস ও ই-কমার্স ইন্টিগ্রেশন।\n\n২. মডুলার ভেক্টর ইউআই রিকনস্ট্রাকশন: প্রতিটি স্ক্রিন, বাটন, ড্রপডাউন এবং চ্যাট বাবলকে ইলাস্ট্রেটরে স্কেলেবল ভেক্টর লেয়ারে নিখুঁতভাবে তৈরি করা হয়েছে, যাতে আল্ট্রা-এইচডি ডিসপ্লেতেও প্রতিটি টেক্সট ও আইকন ক্রিস্টাল ক্লিয়ার থাকে।\n\n৩. স্ন্যাপি কিফ্রেম ও ভেলোসিটি কার্ভস: আফটার ইফেক্টসের গ্রাফ এডিটর ব্যবহার করে অর্গানিক ইলাস্টিক বাউন্স, স্মুথ ইজিং ও স্ন্যাপি ট্রানজিশন তৈরি করা হয়েছে, যা দর্শকদের দৃষ্টিকে স্বাভাবিকভাবে এক ফিচার থেকে অন্য ফিচারে প্রবাহিত করে।\n\n৪. ভিজ্যুয়াল মেটাফোর ও আইকনোগ্রাফি: ইমেল প্রোভাইডার কানেকশন, ডুপ্লিকেট মার্জিং এবং এআই রেসপন্স তৈরির ক্ষেত্রে জটিল টেকনিক্যাল ধারণাকে সহজ ও উপভোগ্য অ্যানিমেশনে রূপান্তর করা হয়েছে।\n\n৫. সাউন্ড ডিজাইন ও অডিও সিঙ্ক: প্রতিটি বাটন ক্লিক, পপ-আপ, কার্ড সোয়াইপ এবং ট্রানজিশনের জন্য নিখুঁত সাউন্ড এফেক্টস (foley, whooshes, UI pops) লেয়ারিং করা হয়েছে, যা ভয়েসওভারের গতি ও মেজাজের সাথে নিখুঁতভাবে সিঙ্ক হয়ে সর্বোচ্চ রিটেনশন নিশ্চিত করে।",
		'solution_en' => "1. Three-Act Narrative Architecture: Segmented the 3-minute script into a structured narrative arc: (a) Shared Inbox foundation, team onboarding, and ticket delegation; (b) Workflow automation, canned responses, spam filtering, private notes, and AI drafting; (c) Live chat assistants, real-time visitor monitoring, self-service knowledge bases, and WordPress/e-commerce integrations.\n\n2. Modular Vector UI Reconstruction: Vectorized every screen, ticket row, dropdown modal, and widget icon in Adobe Illustrator, ensuring razor-sharp high-DPI rendering and clean layout hierarchy without visual clutter.\n\n3. Velocity Curves & Fluid Transitions: Rigged dynamic motion inside Adobe After Effects using custom speed graph curves, kinetic pops, and camera pans that guide viewer eye tracking effortlessly across dense interface workflows.\n\n4. Visual Metaphor Orchestration: Translated technical concepts—such as multi-provider email synchronization, internal notes/@mentions, ticket deduplication, and automated routing rules—into engaging kinetic sequences.\n\n5. Layered UI Sound Design: Engineered bespoke micro-audio cues (clicks, whooshes, notification chimes, and swooshes) synchronized with screen transformations, elevating brand polish and auditory immersion.",
		'highlights_bn'=> array(
			'৩ মিনিটে থ্রাইভডেস্কের সম্পূর্ণ কাস্টমার সাপোর্ট প্ল্যাটফর্মের আকর্ষণীয় উপস্থাপন',
			'শেয়ার্ড ইনবক্স, টিকেট ডেলিগেশন ও টিম কোলাবোরেশনের স্ন্যাপি মোশন',
			'এআই রেসপন্স ড্রাফটিং, ক্যানড রিপ্লাই ও অটোমেশন রুলসের ভিজ্যুয়াল ওয়াকথ্রু',
			'লাইভ চ্যাট উইজেট, রিয়েল-টাইম ভিজিটর ট্র্যাকিং ও নলেজবেস অ্যানিমেশন',
			'ওয়ার্ডপ্রেস, WooCommerce ও Shopify ই-কমার্স ইন্টিগ্রেশনের পরিষ্কার চিত্রায়ন',
			'কাস্টম ভেক্টর ইউআই ডিজাইন ও আফটার ইফেক্টস গ্রাফ এডিটর ভেলোসিটি স্মুথিং',
			'প্রফেশনাল সাউন্ড ডিজাইন ও ভয়েসওভারের সাথে নিখুঁত বিট-সিঙ্ক'
		),
		'highlights_en'=> array(
			'Comprehensive 3-minute SaaS overview animating ThriveDesk’s complete support suite',
			'Dynamic visualization of shared inboxes, ticket assignment, and team collaboration',
			'Kinetic walkthrough of AI drafting, canned responses, and workflow automations',
			'Animated presentation of live chat assistant widgets, visitor monitoring, and knowledge bases',
			'Clear depiction of WordPress, WooCommerce, and Shopify native integrations',
			'Full vector UI reconstruction with After Effects velocity curve smoothing',
			'Precision UI sound design synchronized with motion transitions and voiceover cadence'
		),
		'tags'        => array( 'After Effects', 'Illustrator', 'SaaS Explainer', 'Motion Graphics', '2D Animation', 'Sound Design' ),
		'accent'      => '#6c5ce7',
		'icon'        => 'video',
		'image'       => get_template_directory_uri() . '/assets/img/thrivedesk.png',
		'action_type' => 'video',
		'action_bn'   => 'ইউটিউবে ভিডিওটি দেখুন',
		'action_en'   => 'Watch Video on YouTube',
		'direct_url'  => 'https://youtu.be/ff0j6OYG4Ms',
	),
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
		'id'          => 'quiet-scroll',
		'category'    => 'tools',
		'type_bn'     => 'ক্রোম ও এজ এক্সটেনশন',
		'type_en'     => 'Chrome & Edge Extension',
		'badge_bn'    => 'Manifest V3 • ওপেন সোর্স',
		'badge_en'    => 'Manifest V3 • Open Source',
		'title_bn'    => 'QuietScroll — স্মার্ট পার-সাইট মিডিয়া ভলিউম কন্ট্রোল',
		'title_en'    => 'QuietScroll — Smart Per-Site Media Volume Control',
		'summary_bn'  => 'যেকোনো ভিডিও বা অডিও প্লেয়ারে Alt + মাউস হুইল ঘুরিয়ে নিরবচ্ছিন্ন সাউন্ড নিয়ন্ত্রণ, অটোপ্লে থেকে রক্ষা করতে ভলিউম গার্ড এবং প্রতিটি সাইটের জন্য আলাদা ভলিউম মেমোরি মনে রাখার হালকা ক্রোম এক্সটেনশন।',
		'summary_en'  => 'Lightweight Chromium extension for controlling any media volume using Alt + Mouse Wheel. Features per-site volume memory, MAIN-world Volume Guard, ultra-low presets, and instant Night Mode.',
		'role_bn'     => 'একক এক্সটেনশন আর্কিটেক্ট ও ডেভেলপার',
		'role_en'     => 'Solo Extension Architect & Developer',
		'context_bn'  => 'ক্রোমিয়াম ব্রাউজার এক্সটেনশন • Manifest V3 • v1.7',
		'context_en'  => 'Chromium Browser Extension • Manifest V3 • v1.7',
		'challenge_bn'=> "ইউটিউব, ফেসবুক, টুইটার কিংবা বিভিন্ন নিউজ পোর্টালে ভিডিওর অডিও লেভেল একেক সাইটে একেক রকম থাকে। অনেক সাইট স্বয়ংক্রিয়ভাবে ভিডিও অটোপ্লে করে বা ইউজারের নিজস্ব সাউন্ড প্রেফারেন্স ওভাররাইড করে অতিরিক্ত উচ্চ শব্দে বাজতে শুরু করে। তাছাড়া গভীর রাতে হেডফোন দিয়ে শোনার সময় সাধারণ ব্রাউজার স্লাইডারের ১% ভলিউমও অনেক বেশি উচ্চকিত মনে হয়।\n\nকারিগরি দিক থেকে প্রধান চ্যালেঞ্জ ছিল: পেজের স্বাভাবিক স্ক্রলে কোনো প্রকার ব্যাঘাত না ঘটিয়ে কিংবা ফুলস্ক্রিন প্লেয়ার নষ্ট না করে যেকোনো HTML5 ভিডিও/অডিও প্লেয়ারের অডিও স্ট্রিম ইন্টারসেপ্ট করা, অটোপ্লে প্লেয়ারের জোরপূর্বক সাউন্ড পরিবর্তন প্রতিহত করা এবং কোনো ট্র্যাকিং ছাড়াই সম্পূর্ণ লোকাল স্টোরেজে ডোমেইন ভিত্তিক ভলিউম মেমোরি ধরে রাখা।",
		'challenge_en'=> "Web video and audio players across platforms (YouTube, Twitter/X, news portals) suffer from wildly inconsistent mixing levels and aggressive autoplay volume resets. Furthermore, standard volume ladders lack the granular resolution needed for ultra-sensitive in-ear monitors (IEMs) during late-night listening.\n\nThe engineering challenge was intercepting mousewheel gestures strictly over media elements without disrupting normal vertical scrolling or breaking fullscreen APIs, neutralizing third-party player script overrides in the browser's MAIN world, and maintaining persistent per-origin state with zero telemetry under Manifest V3 restrictions.",
		'solution_bn' => "১. Alt + মাউস হুইল জেসচার ইন্টারসেপশন: পেজের সাধারণ স্ক্রলে কোনো ব্যাঘাত না ঘটিয়ে শুধুমাত্র Alt কি চেপে মাউস হুইল ঘুরালে নিখুঁত ভলিউম পরিবর্তন হয় এবং স্ক্রিনে একটি আধুনিক অন-স্ক্রিন ওএসডি (HUD) ভেসে ওঠে।\n\n২. পার-সাইট স্বয়ংক্রিয় ভলিউম মেমোরি: প্রতিটি ওয়েবসাইটের জন্য আলাদা আলাদা ভলিউম লেভেল স্বয়ংক্রিয়ভাবে Chrome Storage API-তে সংরক্ষিত থাকে। পরবর্তীতে সেই সাইটে প্রবেশ করলে ভিডিও নিজে থেকেই কাঙ্ক্ষিত সাউন্ডে প্লে হয়।\n\n৩. আর্কিটেকচারাল ভলিউম গার্ড (MAIN-World Script): কিছু আগ্রাসী প্লেয়ার যাতে জোরপূর্বক ইউজারের ভলিউম রিসেট করতে না পারে, সেজন্য Chrome 111+ এর document_start MAIN-ওয়ার্ল্ড কনটেন্ট স্ক্রিপ্ট দিয়ে HTMLMediaElement.prototype.volume প্রোপার্টি ডিসক্রিপ্টর ইন্টারসেপ্ট করে কাঙ্ক্ষিত লেভেল অবিচল লক রাখা হয়।\n\n৪. আল্ট্রা-লো প্রিসেটস (০.১২৫% পর্যন্ত): সূক্ষ্ম ও শান্ত শোনার জন্য পপ-আপে ৬টি কুইক ওয়ান-ক্লিক প্রিসেট: ০.১২৫%, ০.১৮৭৫%, ০.২৫%, ০.৩৭৫%, ০.৫% এবং ১%।\n\n৫. গ্লোবাল ওয়ান-ক্লিক নাইট মোড: পপ-আপ থেকে নাইট মোড অন করলেই সমস্ত ওয়েবসাইটের ভলিউম এক নিমেষে নির্ধারিত শান্ত স্তরে নেমে আসে। অফ করলে প্রতিটি সাইট তার নিজস্ব আগের মেমোরি ফিরে পায়।\n\n৬. শতভাগ প্রাইভেট ও অফলাইন: এক্সটেনশনটি কোনো অ্যানালিটিক্স বা ট্র্যাকিং স্ক্রিপ্ট ব্যবহার করে না। সমস্ত ডাটা ইউজারের নিজস্ব ব্রাউজারে সম্পূর্ণ বিচ্ছিন্ন ও সুরক্ষিত থাকে।",
		'solution_en' => "1. Alt + Mouse Wheel Gesture Interception: Transparently hooks wheel events exclusively when Alt is depressed, calculating proportional audio steps and projecting a sleek on-screen HUD without interfering with normal vertical page scroll.\n\n2. Persistent Per-Domain Volume Memory: Utilizes chrome.storage.local to map domain origins to custom volume preferences, automatically applying remembered levels on DOM navigation.\n\n3. Architectural Volume Guard (MAIN-World Script): Implemented a document_start script running in the browser's MAIN world to wrap HTMLMediaElement.prototype.volume property descriptors, neutralizing aggressive autoplay overrides by third-party web players.\n\n4. Ultra-Low Acoustic Presets: Engineered sub-linear stepping down to 0.125%, 0.1875%, 0.25%, and 0.5% tailored for high-sensitivity in-ear monitors (IEMs) and late-night listening.\n\n5. Global One-Click Night Mode: Instantly caps all active tabs and domain profiles to a preconfigured quiet ceiling without overwriting individual site memories.\n\n6. 100% Offline & Zero-Telemetry: Designed with strict Manifest V3 permissions (storage, activeTab), zero remote script dependencies, and absolute local data isolation.",
		'highlights_bn'=> array(
			'Alt + মাউস হুইল দিয়ে যেকোনো ওয়েব মিডিয়া প্লেয়ারের শব্দ নিখুঁতভাবে নিয়ন্ত্রণের সুবিধা',
			'প্রতিটি ওয়েবসাইটের জন্য আলাদা ভলিউম স্বয়ংক্রিয়ভাবে মনে রাখার স্মার্ট মেমোরি সিস্টেম',
			'ভলিউম গার্ড: অটোপ্লে ভিডিও সাইটগুলোর জোরপূর্বক সাউন্ড পরিবর্তন প্রতিহত করার আর্কিটেকচার',
			'গভীর রাতে শোনার জন্য ০.১২৫% পর্যন্ত আল্ট্রা-লো ভলিউম প্রিসেট',
			'এক ক্লিকে সমস্ত সাইট শান্ত করার ডেডিকেটেড নাইট মোড (Night Mode)',
			'গুগল ক্রোম Manifest V3 স্ট্যান্ডার্ডের সাথে ১০০% সামঞ্জস্যপূর্ণ',
			'১০০% অফলাইন ও প্রাইভেট — কোনো ট্র্যাকিং নেই, কোনো এক্সটার্নাল সার্ভার কল নেই'
		),
		'highlights_en'=> array(
			'Alt + Mouse Wheel gesture control over any HTML5 video or audio player',
			'Intelligent per-site volume memory persisted across browser sessions',
			'MAIN-world Volume Guard engine preventing aggressive autoplay resets',
			'Precision ultra-low audio presets stepping down to 0.125% for late-night listening',
			'Global One-Click Night Mode toggle with non-destructive volume restoration',
			'Full compliance with Google Chrome Manifest V3 modern extension standards',
			'100% private and offline: zero tracking, zero analytics, zero external network requests'
		),
		'tags'        => array( 'Chrome Extension', 'Manifest V3', 'JavaScript (ES6+)', 'Chrome Storage API', 'Audio Engineering' ),
		'accent'      => '#6c5ce7',
		'icon'        => 'extension',
		'image'       => get_template_directory_uri() . '/assets/img/quietscroll.png',
		'image_fit'   => 'contain',
		'action_type' => 'code',
		'action_bn'   => 'সোর্স কোড (GitHub)',
		'action_en'   => 'View Source on GitHub',
		'direct_url'  => 'https://github.com/raisulsohan/QuietScroll',
		'github_url'  => 'https://github.com/raisulsohan/QuietScroll',
	),
	array(
		'id'          => 'tickersnap',
		'category'    => 'tools',
		'type_bn'     => 'ক্রোম এক্সটেনশন',
		'type_en'     => 'Chrome Extension',
		'badge_bn'    => 'Manifest V3 • ডুয়েল এক্সট্র্যাক্টর',
		'badge_en'    => 'Manifest V3 • Dual Extractor',
		'title_bn'    => 'TickerSnap — ফুটবল কমেন্টারি ও আর্টিকেল টেক্সট এক্সট্র্যাক্টর',
		'title_en'    => 'TickerSnap — Match Commentary & Article Text Extractor',
		'summary_bn'  => 'ফুটবল ম্যাচ চলাকালীন লাইভ টেক্সট কমেন্টারি (FotMob ও Sofascore) এবং মোজিলা রিড্যাবিলিটি ইঞ্জিনের সাহায্যে যেকোনো নিউজ আর্টিকেল, পিডিএফ ও গুগল ডক্স থেকে বিজ্ঞাপনমুক্ত ক্লিন টেক্সট এক ক্লিকে এক্সট্র্যাক্ট করার ব্রাউজার এক্সটেনশন।',
		'summary_en'  => 'A high-performance Chrome extension featuring two one-click extractors: automated live football match commentary from FotMob/Sofascore and distraction-free article text powered by Mozilla\'s Readability.js engine.',
		'role_bn'     => 'একক এক্সটেনশন ডিজাইনার ও ডেভেলপার',
		'role_en'     => 'Solo Extension Architect & Developer',
		'context_bn'  => 'ক্রোমিয়াম ব্রাউজার এক্সটেনশন • Manifest V3',
		'context_en'  => 'Chromium Browser Extension • Manifest V3',
		'challenge_bn'=> "ফুটবল ম্যাচ চলাকালীন লাইভ টেক্সট কমেন্টারি সাধারণত লেজি-লোড (Lazy-load) হয়ে ধাপে ধাপে আসে এবং বিভিন্ন সাইটে (FotMob, Sofascore) ডম স্ট্রাকচার ও ক্লাসনেম ঘন ঘন পরিবর্তিত হয়। ফলে সাধারণ সাইট-স্পেসিফিক সিএসএস সিলেক্টর দিয়ে কমেন্টারি স্ক্র্যাপ করতে গেলে কোড ভেঙে যায়।\n\nঅন্য দিকে, আধুনিক ওয়েব আর্টিকেল, ব্লগ এবং অনলাইন পিডিএফগুলোতে বিজ্ঞাপন, সাইডবার, ট্র্যাকিং ব্যানার ও জটিল নেভিগেশনের ভিড়ে মূল টেক্সট আলাদা করা দুরূহ। একই সাথে ব্যবহারকারীর ব্রাউজিং প্রাইভেসি রক্ষা করে কোনো সাইট-ওয়াইড পারমিশন ছাড়া এবং কোনো দূরবর্তী সার্ভারে ডেটা না পাঠিয়ে সম্পূর্ণ ব্রাউজারের ভেতর টেক্সট এক্সট্র্যাক্ট করা ছিল প্রধান চ্যালেঞ্জ।",
		'challenge_en'=> "Live football commentary feeds on platforms like FotMob and Sofascore are continuously lazy-loaded, dynamically rendered, and frequently change their DOM class signatures. Hardcoded, site-specific CSS selectors break easily and fail to capture full match timelines.\n\nSimultaneously, extracting clean prose from articles, blogs, Google Docs, and web PDFs is heavily obstructed by aggressive ad banners, paywall overlays, navigation clutter, and custom pagination. The challenge was building an adaptive dual-mode extraction engine that operates strictly client-side under Chromium's activeTab privacy sandbox without full-host permissions.",
		'solution_bn' => "১. ক্লাস্টার-হিওরিস্টিক কমেন্টারি অ্যালগরিদম: কোনো নির্দিষ্ট ক্লাস বা হার্ডকোডেড সিলেক্টরের ওপর নির্ভর না করে পেজের রিয়েল সেন্টেন্স ব্লকগুলোকে কার্ডে গ্রুপ করে এবং সবচেয়ে ঘন ক্লাস্টারটিকে স্বয়ংক্রিয়ভাবে কমেন্টারি ফিড হিসেবে শনাক্ত করে। সাথে স্বয়ংক্রিয় পেজ স্ক্রলিংয়ের মাধ্যমে লেজি-লোডেড পুরনো এন্ট্রিগুলোও নিখুঁতভাবে সংগ্রহ করে।\n\n২. মোজিলা রিড্যাবিলিটি (Readability.js) ইন্টিগ্রেশন: ফায়ারফক্স রিডার ভিউয়ের শক্তিশালী ইঞ্জিন ব্যবহার করে যেকোনো নিউজ সাইট, ব্লগ, স্টোরি পোর্টাল কিংবা অনলাইন পিডিএফ ও গুগল ডক্স থেকে সব জঞ্জাল দূর করে শুধুমাত্র মূল কনটেন্ট নিষ্কাশন।\n\n৩. মিনিট স্ট্যাম্প ও ফরম্যাটিং প্রিজারভেশন: ম্যাচের মিনিট স্ট্যাম্প (যেমন: ৪৫', ৯০+৩') এবং হাফ-টাইম বুলেট সামারি হুবহু ফরম্যাট বজায় রেখে সাজিয়ে দেয়।\n\n৪. রিয়েল-টাইম প্রিভিউ ও সেশন পারসিস্টেন্স: পপ-আপে স্ক্রলেবল লাইভ প্রিভিউ, ওয়ার্ড ও ক্যারেক্টার কাউন্টার প্রদর্শন। পপ-আপ বন্ধ করে দিলেও ক্যাপচার করা টেক্সট ব্রাউজার সেশন জুড়ে মেমোরিতে অক্ষুণ্ণ থাকে।\n\n৫. ওয়ান-ক্লিক কপি ও .txt ডাউনলোড: সংগৃহীত টেক্সট নিমেষেই ক্লিপবোর্ডে কপি করা কিংবা ফাইল আকারে ডাউনলোড করার সুবিধা।\n\n৬. activeTab সিকিউরিটি মডেল: ব্রাউজারের কোনো সাইট-ওয়াইড পারমিশন ওয়ার্নিং নেই; ব্যবহারকারী ক্লিক করলেই কেবল নির্দিষ্ট ট্যাবে এক্সটেনশন কাজ করে এবং সমস্ত টেক্সট ১০০% ইউজারের ডিভাইসেই প্রসেস হয়।",
		'solution_en' => "1. Density-Cluster Commentary Heuristic: Bypasses fragile CSS selectors by analyzing sentence density clusters across the DOM, identifying the commentary list, and executing programmatic auto-scrolling to accumulate lazy-loaded timelines.\n\n2. Mozilla Readability.js Core: Embeds the proven Mozilla Readability engine (behind Firefox Reader View) to strip ads, sidebars, cookie banners, and navigational clutter, extracting pristine prose from news articles, stories, Google Docs, and PDF.js viewers.\n\n3. Match Minute Precision: Intelligently parses timestamps (e.g., 45', 90+3') and halftime bullet notes across varied typography and apostrophe encodings.\n\n4. Persistent Session Preview Panel: Instant preview with real-time character and word counts; captures survive popup closures and persist across the active browser session.\n\n5. One-Click Copy & .txt File Export: Formats plain-text outputs ready for instant clipboard copying or .txt downloading.\n\n6. Zero-Telemetry activeTab Security: Operates under Chromium's strict activeTab sandbox—only interacts with a page upon explicit user invocation with zero remote servers or telemetry.",
		'highlights_bn'=> array(
			'ডুয়েল এক্সট্র্যাক্টর: ফুটবল কমেন্টারি ও আর্টিকেল রিডার ভিউ একই এক্সটেনশনে',
			'ক্লাস্টার-হিওরিস্টিক ইঞ্জিন: কোনো নির্দিষ্ট ক্লাসের ওপর নির্ভর না করে স্বয়ংক্রিয় কমেন্টারি শনাক্তকরণ',
			'লেজি-লোড অটো-স্ক্রলিং: পুরো ৯০ মিনিটের কমেন্টারি স্বয়ংক্রিয়ভাবে স্ক্রল করে ক্যাপচার',
			'মোজিলা Readability.js পাওয়ারড: যেকোনো ওয়েব পেজ, পিডিএফ ও গুগল ডক্স থেকে ক্লিন টেক্সট',
			'রিয়েল-টাইম স্ক্রলেবল প্রিভিউ, ওয়ার্ড কাউন্টার ও সেশন পারসিস্টেন্স',
			'এক ক্লিকে ক্লিপবোর্ডে কপি ও .txt ফাইল ডাউনলোডের সুবিধা',
			'activeTab পারমিশন: সম্পূর্ণ ১০০% অফলাইন ও ব্যক্তিগত গোপনীয়তা রক্ষা'
		),
		'highlights_en'=> array(
			'Dual Extraction Engine: Live match commentary & clean article reading view in one tool',
			'Density-cluster heuristics: Identifies commentary feeds without fragile hardcoded selectors',
			'Automated lazy-load scrolling: Accumulates full 90+ minute timelines seamlessly',
			'Powered by Mozilla Readability.js: Cleans ads and sidebars from articles, PDFs, and Docs',
			'Live scrollable preview with word/character counter and persistent session memory',
			'Instant One-Click Clipboard Copy and .txt file download',
			'Chromium activeTab security: 100% client-side execution with zero external data transfer'
		),
		'tags'        => array( 'Chrome Extension', 'Manifest V3', 'Readability.js', 'DOM Heuristics', 'Text Extraction' ),
		'accent'      => '#00b894',
		'icon'        => 'extension',
		'image'       => get_template_directory_uri() . '/assets/img/tickersnap.png',
		'image_fit'   => 'contain',
		'action_type' => 'code',
		'action_bn'   => 'সোর্স কোড (GitHub)',
		'action_en'   => 'View Source on GitHub',
		'direct_url'  => 'https://github.com/raisulsohan/Tickersnap',
		'github_url'  => 'https://github.com/raisulsohan/Tickersnap',
	),
	array(
		'id'          => 'ruler-for-browser',
		'category'    => 'tools',
		'type_bn'     => 'ক্রোম ও এজ এক্সটেনশন',
		'type_en'     => 'Chrome & Edge Extension',
		'badge_bn'    => 'Manifest V3 • ডিজাইন ও মেজারমেন্ট',
		'badge_en'    => 'Manifest V3 • Precision Ruler & Snapping',
		'title_bn'    => 'Ruler for Browser — ফটোশপ স্টাইল রুলার, স্ন্যাপিং গাইড ও মেজারমেন্ট',
		'title_en'    => 'Ruler for Browser — Photoshop-Style Rulers, Draggable Guides & On-Page Measurement',
		'summary_bn'  => 'যেকোনো ওয়েব পেজের ওপর ফটোশপ ধাঁচের অনুভূমিক ও উল্লম্ব রুলার, ড্র্যাগ-অ্যান্ড-ড্রপ গাইডলাইন, রিয়েল ডম এলিমেন্ট স্ন্যাপিং এবং নিখুঁত ডিস্ট্যান্স মেজারমেন্টের লাইটওয়েট ক্রোম এক্সটেনশন।',
		'summary_en'  => 'Lightweight Chromium extension projecting Photoshop-style rulers, draggable guide overlays, smart element-edge snapping, and real-time pixel distance measurement directly over any webpage.',
		'role_bn'     => 'একক এক্সটেনশন ডিজাইনার ও ডেভেলপার',
		'role_en'     => 'Solo Extension Architect & Developer',
		'context_bn'  => 'ক্রোমিয়াম ব্রাউজার এক্সটেনশন • Manifest V3 • v1.0',
		'context_en'  => 'Chromium Browser Extension • Manifest V3 • v1.0',
		'challenge_bn'=> "ওয়েব UI ডেভেলপমেন্ট এবং ডিজাইন কিউএ (QA) অডিটের সময় বিভিন্ন উপাদানের মার্জিন, প্যাডিং কিংবা অ্যালাইনমেন্ট নিখুঁত আছে কি না তা যাচাই করা বেশ কঠিন। সাধারণত এজন্য ফুল-পেজ স্ক্রিনশট নিয়ে ফিগমা বা ফটোশপে নিয়ে মাপতে হয়, যা কাজের গতি নষ্ট করে।\n\nব্রাউজারের ভেতর সরাসরি রুলার ও গাইডলাইন যুক্ত করার ক্ষেত্রে প্রধান চ্যালেঞ্জ ছিল: মূল ওয়েবসাইটের নিজস্ব সিএসএস স্টাইল বা স্ক্রিপ্ট যাতে এক্সটেনশনের রুলারকে বিকৃত করতে না পারে, পেজ স্ক্রল করার সময় গাইডলাইনগুলো যাতে তাদের আসল ডম এলিমেন্টের সাথে নির্ভুলভাবে পিন্ড থাকে, এলিমেন্টগুলোর এজ (ধার) ও সেন্টারে ৬ পিক্সেলের ম্যাগনেটিক স্ন্যাপিং নিশ্চিত করা, এবং ব্রাউজার রিলোড করলেও ডোমেনভেদে গাইডগুলো স্বয়ংক্রিয়ভাবে রিকল করা।",
		'challenge_en'=> "During web UI development and design QA audits, verifying visual alignment, margin balances, and relative spacing across components conventionally requires capturing screenshots and measuring in Figma or Photoshop, breaking the developer flow.\n\nInjecting measurement overlays directly into arbitrary third-party pages poses significant technical hurdles: host page CSS resets and aggressive stylesheet inheritance bleed into the overlay, arbitrary z-indexes occlude ruler bars, vertical page scrolling displaces coordinates, and DOM shifts disrupt alignment. The challenge was building an isolated, zero-leak measurement overlay with magnetic edge-snapping and persistent document-space guides under Manifest V3.",
		'solution_bn' => "১. শ্যাডো ডম (Shadow DOM) আইসোলেশন: হোস্ট পেজের সিএসএস বা স্ক্রিপ্ট যাতে কোনোভাবেই এক্সটেনশনের স্টাইলকে প্রভাবিত করতে না পারে, সেজন্য সম্পূর্ণ রুলার ইন্টারফেস Shadow Root-এর ভেতরে সম্পূর্ণ বিচ্ছিন্নভাবে রেন্ডার করা হয়েছে।\n\n২. ফটোশপ স্টাইল ড্র্যাগ-অ্যান্ড-ড্রপ গাইড: ওপরের বা বামের রুলার থেকে মাউস ড্র্যাগ করে টানলেই তাত্ক্ষণিক অনুভূমিক বা উল্লম্ব গাইড তৈরি হয়। ড্র্যাগ করে সরানো, ডাবল ক্লিকে বা রুলারে ফেরত নিয়ে ডিলিট করা এবং হটকি (Alt+R, Ctrl+;, Ctrl+Alt+;) দিয়ে সহজে পরিচালনা করা যায়।\n\n৩. ম্যাগনেটিক স্ন্যাপিং ইঞ্জিন (Snap Engine): মাউস ড্র্যাগ করার সময় নিকটবর্তী ডম উপাদানের বাম, ডান, কেন্দ্র কিংবা শীর্ষ, তলদেশ ও মধ্যবিন্দুর ৬ পিক্সেলের মধ্যে ম্যাগনেটিক স্ন্যাপ করে (Shift কি চেপে স্ন্যাপ সাময়িক অফও রাখা যায়)।\n\n৪. Alt-কি ডাইমেনশন ও ডিস্ট্যান্স মেজারমেন্ট: Alt চেপে মাউস হোভার করলেই উপাদানের সাইজ ব্যাজ দেখা যায় এবং ড্র্যাগ করলে উপাদানগুলোর মধ্যকার রিয়েল-টাইম পিক্সেল দূরত্ব নিঁখুতভাবে ডিসপ্লে হয়।\n\n৫. ৩টি অ্যাডাপ্টিভ ডিসপ্লে মোড: ফ্লোটিং Overlay মোড ছাড়াও Push মোডে পেজ কনটেন্ট ২২ পিক্সেল নিচে নেমে আসে যাতে কোনো হেডার ঢাকা না পড়ে। আর Auto-hide মোডে মাউস স্ক্রিনের প্রান্তে গেলে রুলার মসৃণভাবে ভেসে ওঠে।\n\n৬. স্ক্রল-রেসিলিয়েন্ট পার-অরিজিন লোকাল স্টোরেজ: গাইডগুলো ডকুমেন্ট কোঅর্ডিনেটে কাজ করায় পেজ স্ক্রল করলেও উপাদানগুলোর সাথে আটকে থাকে। এছাড়া প্রতিটি ওয়েবসাইটের গাইড লোকাল মেমরিতে সংরক্ষিত থাকে, ফলে পেজ রিলোড করলেও গাইড অক্ষত থাকে।",
		'solution_en' => "1. Shadow DOM Encapsulation: Hosts all ruler tracks, guide markers, and HUD controls within a closed Shadow Root, entirely preventing host page CSS bleed, style pollution, and inheritance collisions.\n\n2. Native Photoshop-Style Guide Mechanics: Dragging down from the top bar or right from the left bar creates persistent guides. Supports real-time dragging, double-click deletion, ruler flick discarding, and ergonomic hotkeys (Alt+R, Ctrl+; for hide/show, Ctrl+Alt+; for locking).\n\n3. Spatial Snap Heuristics: Evaluates on-screen viewport elements in real time, snapping guides to element boundaries (left, right, center, top, bottom, middle) within a 6px threshold, bypassable on-the-fly via Shift key.\n\n4. Alt-Key Distance & Dimension Measurement: Pressing Alt inspects hovered element bounds with live dimension badges, while dragging casts relative distance readouts between disparate page components.\n\n5. Three Adaptable View Modes: Supports default Floating Overlay, Content Push (offsetting document content by 22px to prevent header obstruction), and Edge Proximity Auto-hide (revealing rulers smoothly on hover near viewport boundaries).\n\n6. Document-Pinned Per-Origin Persistence: Anchors guides to absolute document coordinates so markers stay attached during page scrolling, stored locally per domain origin via chrome.storage.local with 100% offline security.",
		'highlights_bn'=> array(
			'ফটোশপ ধাঁচের ড্র্যাগ-অ্যান্ড-ড্রপ অনুভূমিক ও উল্লম্ব গাইডলাইন',
			'নিকটবর্তী ডম উপাদানের এজ ও সেন্টারে ৬px ম্যাগনেটিক স্ন্যাপিং',
			'Alt কি চেপে ইনস্ট্যান্ট এলিমেন্ট ডাইমেনশন ও পিক্সেল দূরত্ব পরিমাপ',
			'শ্যাডো ডম (Shadow DOM) দিয়ে মূল সাইটের সিএসএস থেকে ১০০% বিচ্ছিন্ন',
			'৩টি ডিসপ্লে মোড: ফ্লোটিং ওভারলে, কনটেন্ট পুশ (Push) এবং অটো-হাইড (Auto-hide)',
			'স্ক্রল-রেসিলিয়েন্ট ডকুমেন্ট কোঅর্ডিনেট ও পার-ডোমেইন অটো-সেভ',
			'১০০% অফলাইন ও ব্যক্তিগত গোপনীয়তা রক্ষা — কোনো দূরবর্তী সার্ভার কল নেই'
		),
		'highlights_en'=> array(
			'Photoshop-style draggable horizontal and vertical guide overlays',
			'Smart magnetic snapping to element boundaries and centers within 6px',
			'Alt-key real-time element dimension inspection and distance readout',
			'Encapsulated inside Shadow DOM preventing host stylesheet conflicts',
			'3 flexible display modes: Overlay, Content Push, and Edge Auto-hide',
			'Scroll-persistent document coordinates with per-origin local storage',
			'Zero analytics or network calls: 100% private client-side execution'
		),
		'tags'        => array( 'Chrome Extension', 'Manifest V3', 'Shadow DOM', 'DOM Snapping', 'UI/UX Measurement' ),
		'accent'      => '#0984e3',
		'icon'        => 'extension',
		'image'       => get_template_directory_uri() . '/assets/img/rulerforbrowser.png',
		'image_fit'   => 'contain',
		'action_type' => 'code',
		'action_bn'   => 'সোর্স কোড (GitHub)',
		'action_en'   => 'View Source on GitHub',
		'direct_url'  => 'https://github.com/raisulsohan/RulerForBrowser',
		'github_url'  => 'https://github.com/raisulsohan/RulerForBrowser',
	),
	array(
		'id'          => 'lazy-image-ae',
		'category'    => 'tools',
		'type_bn'     => 'আফটার ইফেক্টস এক্সটেনশন ও ব্রিজ',
		'type_en'     => 'After Effects CEP & Chrome Bridge',
		'badge_bn'    => 'Adobe CEP • AI ওয়ার্কফ্লো',
		'badge_en'    => 'Adobe CEP • AI Motion Workflow',
		'title_bn'    => 'Lazy-Image — আফটার ইফেক্টস নেটিভ AI ইমেজ জেনারেটর',
		'title_en'    => 'Lazy-Image — Native AI Image Generation Inside Adobe After Effects',
		'summary_bn'  => 'কোনো API ফি বা অতিরিক্ত খরচ ছাড়া ব্রাউজারের সক্রিয় ChatGPT সেশন ব্যবহার করে সরাসরি আফটার ইফেক্টস প্যানেলে এআই ছবি তৈরি, প্রজেক্ট ফোল্ডারে সেভ এবং স্বয়ংক্রিয়ভাবে অ্যাক্টিভ কম্পোজিশন টাইমলাইনে প্লেহেডে লেয়ার হিসেবে ইনসার্ট করার অ্যাডোবি CEP এক্সটেনশন।',
		'summary_en'  => 'An Adobe After Effects CEP extension and Chrome companion bridge enabling zero-cost AI image generation directly inside After Effects via your active ChatGPT session—saving assets to your project directory and auto-placing them onto the active timeline at the playhead.',
		'role_bn'     => 'একক সিস্টেম আর্কিটেক্ট ও ক্রিয়েটিভ টুলস ইঞ্জিনিয়ার',
		'role_en'     => 'Solo System Architect & Creative Tools Engineer',
		'context_bn'  => 'অ্যাডোবি আফটার ইফেক্টস প্লাগইন • CEP + ExtendScript + MV3',
		'context_en'  => 'Adobe After Effects Extension • CEP + ExtendScript + MV3',
		'challenge_bn'=> "মোশন ডিজাইনার ও ভিজ্যুয়াল আর্টিস্টদের কনসেপ্ট আর্ট, ব্যাকগ্রাউন্ড কিংবা টেক্সচার তৈরির জন্য প্রতিনিয়ত ব্রাউজারে মিডজার্নি বা চ্যাটজিপিটিতে প্রম্পট দিয়ে ছবি তৈরি করতে হয়, তা ডাউনলোড করে ফোল্ডার খুঁজে আফটার ইফেক্টসের প্রজেক্ট বিনে ইমপোর্ট করতে হয় এবং সেখান থেকে ড্র্যাগ করে কম্পোজিশন টাইমলাইনের সঠিক সময়ে বসাতে হয়। বারবার উইন্ডো পরিবর্তন এবং ম্যানুয়াল ফাইল হ্যান্ডলিং কাজের গতি মারাত্মকভাবে ধীর করে দেয়। তাছাড়া অফিসিয়াল API ব্যবহার করতে গেলে অতিরিক্ত সাবস্ক্রিপশন ও পেইড টোকেন খরচ হয়।\n\nকারিগরি দিক থেকে প্রধান চ্যালেঞ্জ ছিল তিনটি ভিন্ন রানটাইম আর্কিটেকচারের মধ্যে নিরবচ্ছিন্ন ডেটা ও কন্ট্রোল পাইপলাইন প্রতিষ্ঠা করা: আফটার ইফেক্টসের ভেতরের ক্রোমিয়াম-বেসড CEP প্যানেল (Node.js), ব্যবহারকারীর ক্রোম ব্রাউজারের Manifest V3 সার্ভিস ওয়ার্কার এবং অ্যাডোবি আফটার ইফেক্টসের অভ্যন্তরীণ ExtendScript (C++) ইঞ্জিন।",
		'challenge_en'=> "Motion designers and visual effects artists routinely need conceptual backgrounds, storyboards, and texture assets during editing. The standard workflow demands juggling browser tabs, generating imagery via separate web interfaces, downloading files, locating them on disk, importing them into the After Effects project bin, and manually dragging them onto the timeline at the playhead. This repetitive context-switching cripples creative momentum, while official commercial APIs introduce recurring per-token billing.\n\nThe engineering challenge was bridging three isolated runtime environments: Adobe's internal CEP Chromium panel (with Node.js disk access), a Chromium Manifest V3 service worker executing in the user's personal browser with an active ChatGPT session, and the native Adobe ExtendScript C++ scripting engine driving the After Effects project timeline.",
		'solution_bn' => "১. ট্রাই-লেয়ার মাইক্রো-সার্ভিস আর্কিটেকচার: আফটার ইফেক্টসের ভেতরের CEP প্যানেলে একটি সুরক্ষিত লোকাল লুপব্যাক HTTP সার্ভার (127.0.0.1:7890) রান করে। ক্রোম এক্সটেনশনের ব্যাকগ্রাউন্ড সার্ভিস ওয়ার্কার এই লোকাল সার্ভারের সাথে যোগাযোগ করে ব্রাউজারে সক্রিয় ChatGPT ট্যাবে প্রম্পট ও অ্যাসপেক্ট রেশিও পাঠায় এবং ডম অটোমেশনের মাধ্যমে উচ্চমানের ইমেজ স্ট্রিম সংগ্রহ করে।\n\n২. স্বয়ংক্রিয় টাইমলাইন ইনসার্শন (ExtendScript Automation): ছবি জেনারেট হওয়ামাত্রই ExtendScript ইঞ্জিনের মাধ্যমে আফটার ইফেক্টসে সক্রিয় ওপেন কম্পোজিশনের বর্তমান প্লেহেড পজিশনে (comp.time) লেয়ার হিসেবে স্বয়ংক্রিয়ভাবে প্লেস করা হয়—ব্যবহারকারীকে কোনো ম্যানুয়াল ড্র্যাগ-অ্যান্ড-ড্রপ করতে হয় না।\n\n৩. স্মার্ট প্রজেক্ট ফাইল অর্গানাইজেশন: এক্সটেনশনটি স্বয়ংক্রিয়ভাবে আফটার ইফেক্টসের সেভ করা .aep প্রজেক্ট ফোল্ডার শনাক্ত করে এবং তার ভেতরে একটি সুসজ্জিত AI_Generated সাবফোল্ডার তৈরি করে ছবিগুলো স্বয়ংক্রিয়ভাবে সংরক্ষণ করে (প্রজেক্ট সেভ না থাকলে নিরাপদে ডকুমেন্টস ফোল্ডারে ব্যাকআপ রাখে)।\n\n৪. অ্যাসপেক্ট রেশিও প্রিসেট ও মাল্টি-ল্যাঙ্গুয়েজ প্রম্পট: ওয়ান-ক্লিকে ১:১, ১৬:৯, ৯:১৬ এবং ৪:৫ ছাড়াও কাস্টম রেজোলিউশন সাপোর্ট। ইউনিকোড এনকোডিংয়ের ফলে বাংলা, ইংরেজি সহ যেকোনো ভাষায় প্রম্পট দেওয়া যায়।\n\n৫. নো-এপিআই কস্ট জিরো-বিলিং: কোনো পেইড API কি বা ক্রেডিট ছাড়াই ইউজারের নিজস্ব ক্রোম ব্রাউজারের ফ্রি কিংবা প্লাস চ্যাটজিপিটি লগইন সেশন কাজে লাগিয়ে নিখরচায় ছবি তৈরি করা যায়।\n\n৬. ওয়ান-ক্লিক অটো-ইনস্টলার: রেজিস্ট্রিতে PlayerDebugMode সক্রিয় করা এবং উইন্ডোজ সিম্বলিক লিঙ্কের মাধ্যমে আফটার ইফেক্টস এক্সটেনশন ডিরেক্টরিতে প্লাগইন লিংক করার জন্য একটি স্বয়ংক্রিয় .bat স্ক্রিপ্ট অন্তর্ভুক্ত রয়েছে।",
		'solution_en' => "1. Tri-Layer Micro-Service Architecture: The CEP panel runs an internal Node.js loopback HTTP server on 127.0.0.1:7890. A companion Manifest V3 Chrome extension polls tasks, injects prompts and aspect ratios into an active chatgpt.com session via DOM automation, and streams the high-resolution image back as base64.\n\n2. Automatic Timeline Injection (ExtendScript): Once retrieved, the Node.js layer writes the asset to disk, invokes the After Effects ExtendScript bridge (host/index.jsx), imports the footage into the project bin, and automatically creates a new layer on the active composition timeline precisely at the current playhead position (comp.time).\n\n3. Intelligent Project File Discovery: Automatically resolves the file path of the currently open .aep project and organizes generated imagery neatly inside a dedicated <ProjectDir>/AI_Generated/ subfolder (with fallback to Documents/GImage_Generated/).\n\n4. Preset Aspect Ratios & Full Unicode Prompts: Instant one-click selection for 1:1, 16:9, 9:16, 4:5 ratios plus custom dimension inputs, fully supporting multi-language prompts (Bengali, English, etc.) without character corruption.\n\n5. Zero API Costs: Operates without API key subscriptions or per-generation fees by securely tapping into the user's authorized ChatGPT browser session in Chrome.\n\n6. 1-Click Automated Windows Installer: Includes an install.bat utility enabling Adobe CEP PlayerDebugMode in the Windows registry and symlinking the bundle into %APPDATA%\\Adobe\\CEP\\extensions\\ for effortless zero-configuration setup.",
		'highlights_bn'=> array(
			'সরাসরি আফটার ইফেক্টস প্যানেল থেকে ওয়ান-ক্লিকে AI ইমেজ জেনারেট',
			'স্বয়ংক্রিয়ভাবে কম্পোজিশন টাইমলাইনে প্লেহেড পজিশনে লেয়ার ইনসার্ট',
			'কোনো পেইড API কি বা অতিরিক্ত খরচ নেই—বিদ্যমান চ্যাটজিপিটি ব্রাউজার সেশনে সক্রিয়',
			'স্মার্ট .aep প্রজেক্ট ফোল্ডার ডিটেকশন ও ডেডিকেটেড AI_Generated ডিরেক্টরি',
			'১:১, ১৬:৯, ৯:১৬, ৪:৫ অ্যাসপেক্ট রেশিও এবং কাস্টম রেজোলিউশন প্রিসেট',
			'ইউনিকোড ফুল সাপোর্ট: বাংলা বা যেকোনো ভাষায় প্রম্পট দেওয়ার সুবিধা',
			'উইন্ডোজের জন্য ওয়ান-ক্লিক অটো-ইনস্টলার স্ক্রিপ্ট (PlayerDebugMode ও সিমলিঙ্ক)'
		),
		'highlights_en'=> array(
			'Native Adobe After Effects panel for prompt-based AI image generation',
			'Instant auto-placement on active composition timeline at the current playhead',
			'Zero API subscription or credit billing—connects to active browser ChatGPT session',
			'Automated .aep project directory discovery with structured AI_Generated asset bins',
			'One-click aspect ratio presets: 1:1, 16:9, 9:16, 4:5 and custom resolutions',
			'Full Unicode prompt support: seamlessly accepts Bengali, English, and other languages',
			'Streamlined 1-click Windows installer automating registry debug flags and symlinks'
		),
		'tags'        => array( 'Adobe CEP', 'After Effects', 'ExtendScript', 'Chrome Extension', 'AI Automation', 'Node.js' ),
		'accent'      => '#6c5ce7',
		'icon'        => 'extension',
		'image'       => get_template_directory_uri() . '/assets/img/lazyimage.png',
		'action_type' => 'code',
		'action_bn'   => 'সোর্স কোড (GitHub)',
		'action_en'   => 'View Source on GitHub',
		'direct_url'  => 'https://github.com/raisulsohan/LazyImageGeneration',
		'github_url'  => 'https://github.com/raisulsohan/LazyImageGeneration',
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
							<div class="rs-portfolio-card__img-wrap <?php echo ( ! empty( $p['image_fit'] ) && 'contain' === $p['image_fit'] ) ? 'is-contain' : ''; ?>">
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
						<div class="rs-case-study-text" id="rs-modal-challenge"></div>
					</div>

					<!-- The Solution & Process -->
					<div class="rs-case-study-section rs-case-study-box rs-case-study-box--solution">
						<h4 class="rs-case-study-heading">
							💡 <?php echo esc_html( $rs_is_en ? 'The Solution & Creative Process' : 'সমাধান ও কর্মপ্রক্রিয়া' ); ?>
						</h4>
						<div class="rs-case-study-text" id="rs-modal-solution"></div>
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

		<button class="rs-modal__top" type="button" id="rs-case-study-top" aria-label="<?php echo esc_attr( $rs_is_en ? 'Back to top' : 'উপরে যান' ); ?>" title="<?php echo esc_attr( $rs_is_en ? 'Back to top' : 'উপরে যান' ); ?>">
			<?php echo wp_kses( rs_icon( 'up' ), rs_svg_tags() ); ?>
		</button>
	</div>
</div>

<!-- =========================================================================
     Full-Resolution Image Lightbox Overlay
     ====================================================================== -->
<div class="rs-overlay rs-overlay--lightbox" id="rs-image-lightbox" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $rs_is_en ? 'Full Image Preview' : 'ছবির পূর্ণাঙ্গ প্রিভিউ' ); ?>" hidden>
	<button class="rs-lightbox__close" type="button" id="rs-lightbox-close" aria-label="<?php echo esc_attr( $rs_is_en ? 'Close preview' : 'প্রিভিউ বন্ধ করুন' ); ?>" title="<?php echo esc_attr( $rs_is_en ? 'Close (Esc)' : 'বন্ধ করুন (Esc)' ); ?>">
		<?php echo wp_kses( rs_icon( 'close', 18 ), rs_svg_tags() ); ?>
	</button>
	<div class="rs-lightbox__wrap" id="rs-lightbox-wrap">
		<img src="" alt="" class="rs-lightbox__img" id="rs-lightbox-img">
		<div class="rs-lightbox__footer">
			<span class="rs-lightbox__caption" id="rs-lightbox-caption"></span>
			<span class="rs-lightbox__tip"><?php echo esc_html( $rs_is_en ? 'Click anywhere or press Esc to close' : 'যেকোনো স্থানে ক্লিক করে বা Esc চেপে বন্ধ করুন' ); ?></span>
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
	var visualContainer = document.getElementById('rs-modal-visual');
	var visualEl        = document.getElementById('rs-modal-visual-content');
	var actionBtn       = document.getElementById('rs-modal-action-btn');
	var actionLabel     = document.getElementById('rs-modal-action-label');
	var githubBtn       = document.getElementById('rs-modal-github-btn');
	var topBtn          = document.getElementById('rs-case-study-top');
	var lightboxOverlay = document.getElementById('rs-image-lightbox');
	var lightboxClose   = document.getElementById('rs-lightbox-close');
	var lightboxImg     = document.getElementById('rs-lightbox-img');
	var lightboxCaption = document.getElementById('rs-lightbox-caption');

	function renderRichText(container, text) {
		container.innerHTML = '';
		if (!text) return;
		var paras = text.split(/\n\n+/);
		paras.forEach(function(para) {
			var p = document.createElement('p');
			p.innerHTML = para.replace(/\n/g, '<br>');
			container.appendChild(p);
		});
	}

	function openLightbox(src, title) {
		if (!lightboxOverlay || !lightboxImg) return;
		lightboxImg.src = src;
		lightboxImg.alt = title || '';
		if (lightboxCaption) lightboxCaption.textContent = title || '';
		lightboxOverlay.hidden = false;
	}

	function closeLightbox() {
		if (!lightboxOverlay || lightboxOverlay.hidden) return;
		lightboxOverlay.hidden = true;
		if (lightboxImg) lightboxImg.src = '';
	}

	function openCaseStudy(projectId) {
		var p = projectsMap[projectId];
		if (!p || !overlay) return;

		var isEn = document.documentElement.lang.indexOf('en') === 0;

		titleEl.textContent    = p.title;
		badgeEl.textContent    = p.badge;
		typeEl.textContent     = p.type;
		roleEl.textContent     = p.role;
		contextEl.textContent  = p.context;
		summaryEl.textContent  = p.summary;
		renderRichText(challengeEl, p.challenge);
		renderRichText(solutionEl, p.solution);

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

		// Only show secondary GitHub button if GitHub URL is distinct from the primary action link
		if (p.github_url && githubBtn && p.github_url !== p.direct_url) {
			githubBtn.href = p.github_url;
			githubBtn.style.display = 'inline-flex';
			githubBtn.querySelector('span').textContent = isEn ? 'View on GitHub' : 'গিটহাবে কোড দেখুন';
		} else if (githubBtn) {
			githubBtn.style.display = 'none';
		}

		// Dynamic Visual Banner (Real Screenshot or CSS Mockup)
		var displayDomain = p.direct_url.replace(/^https?:\/\//, '').replace(/\/.*$/, '');
		var isZoomable = Boolean(p.image && p.image_fit !== 'contain');

		if (visualContainer) {
			visualContainer.classList.toggle('is-full-view', isZoomable);
		}

		if (p.image) {
			var zoomHint = isZoomable
				? '<div class="rs-case-study-zoom-badge">' +
				  '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>' +
				  '<span>' + (isEn ? 'Click to zoom' : 'বড় করে দেখুন') + '</span>' +
				  '</div>'
				: '';
			var zoomClass = isZoomable ? ' is-zoomable' : '';
			var fitClass = p.image_fit === 'contain' ? ' is-contain' : '';
			var zoomAttrs = isZoomable
				? ' role="button" tabindex="0" title="' + (isEn ? 'Click to view full image' : 'সম্পূর্ণ ছবি দেখতে ক্লিক করুন') + '"'
				: '';

			if (p.category === 'web') {
				visualEl.innerHTML = '<div class="rs-case-study-web-mockup"><div class="rs-portfolio-card__browser-bar"><span class="rs-portfolio-dot"></span><span class="rs-portfolio-dot"></span><span class="rs-portfolio-dot"></span><span class="rs-portfolio-card__url">' + displayDomain + '</span></div><div class="rs-case-study-img-wrap' + fitClass + zoomClass + '"' + zoomAttrs + '><img src="' + p.image + '" alt="' + p.title + '" class="rs-case-study-img">' + zoomHint + '</div></div>';
			} else if (p.category === 'video') {
				var videoZoomBtn = isZoomable
					? '<button type="button" class="rs-case-study-zoom-badge is-clickable" aria-label="' + (isEn ? 'Click to view full image' : 'সম্পূর্ণ ছবি দেখতে ক্লিক করুন') + '" title="' + (isEn ? 'Click to zoom artwork' : 'আর্টওয়ার্ক বড় করে দেখুন') + '">' +
					  '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>' +
					  '<span>' + (isEn ? 'Click to zoom' : 'বড় করে দেখুন') + '</span>' +
					  '</button>'
					: '';
				visualEl.innerHTML = '<div class="rs-case-study-video-wrap">' +
					'<a href="' + p.direct_url + '" target="_blank" rel="noopener noreferrer" class="rs-case-study-video-mockup" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.6)), url(' + p.image + '); background-size: cover; background-position: center;" title="' + (isEn ? 'Watch on YouTube (Opens in new tab)' : 'ইউটিউবে দেখুন (নতুন ট্যাবে খুলবে)') + '">' +
					'<div class="rs-portfolio-card__play-btn"><svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 4 20 12 6 20 6 4"></polygon></svg></div>' +
					'<span class="rs-case-study-video-label">' + (isEn ? 'HD Video Preview • Click to Watch' : 'এইচডি ভিডিও প্রিভিউ • দেখতে ক্লিক করুন') + '</span>' +
					'</a>' +
					videoZoomBtn +
					'</div>';
			} else {
				visualEl.innerHTML = '<div class="rs-case-study-img-wrap' + fitClass + zoomClass + '"' + zoomAttrs + '><img src="' + p.image + '" alt="' + p.title + '" class="rs-case-study-img">' + zoomHint + '</div>';
			}

			if (isZoomable) {
				var zoomTrigger = visualEl.querySelector('.is-zoomable, .rs-case-study-zoom-badge.is-clickable');
				if (zoomTrigger) {
					zoomTrigger.addEventListener('click', function(e) {
						e.stopPropagation();
						e.preventDefault();
						openLightbox(p.image, p.title);
					});
					zoomTrigger.addEventListener('keydown', function(e) {
						if (e.key === 'Enter' || e.key === ' ') {
							e.stopPropagation();
							e.preventDefault();
							openLightbox(p.image, p.title);
						}
					});
				}
			}
		} else {
			if (p.category === 'video') {
				visualEl.innerHTML = '<div class="rs-case-study-video-wrap">' +
					'<a href="' + p.direct_url + '" target="_blank" rel="noopener noreferrer" class="rs-case-study-video-mockup" title="' + (isEn ? 'Watch on YouTube (Opens in new tab)' : 'ইউটিউবে দেখুন (নতুন ট্যাবে খুলবে)') + '">' +
					'<div class="rs-portfolio-card__play-btn"><svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 4 20 12 6 20 6 4"></polygon></svg></div>' +
					'<span class="rs-case-study-video-label">' + (isEn ? 'HD Video Preview • Click to Watch' : 'এইচডি ভিডিও প্রিভিউ • দেখতে ক্লিক করুন') + '</span>' +
					'</a>' +
					'</div>';
			} else if (p.category === 'web') {
				visualEl.innerHTML = '<div class="rs-case-study-web-mockup"><div class="rs-portfolio-card__browser-bar"><span class="rs-portfolio-dot"></span><span class="rs-portfolio-dot"></span><span class="rs-portfolio-dot"></span><span class="rs-portfolio-card__url">' + displayDomain + '</span></div><div class="rs-case-study-web-body"><span>⚡ ' + (isEn ? 'Fast Responsive Zero-Plugin Web Platform' : 'দ্রুতগতির জিরো-প্লাগিন রেসপনসিভ ওয়েবসাইট') + '</span></div></div>';
			} else {
				visualEl.innerHTML = '<div class="rs-case-study-tool-mockup"><div class="rs-case-study-tool-badge">' + (p.icon === 'extension' ? '🧩' : (p.icon === 'terminal' ? '⌨️' : '⚙️')) + '</div><span>' + p.type + '</span></div>';
			}
		}

		// Show Modal
		overlay.hidden = false;
		document.body.style.overflow = 'hidden';
		if (scrollArea) scrollArea.scrollTop = 0;
		if (topBtn) topBtn.classList.remove('is-visible');
	}

	function closeCaseStudy() {
		closeLightbox();
		if (!overlay || overlay.hidden) return;
		overlay.hidden = true;
		document.body.style.overflow = '';
		if (topBtn) topBtn.classList.remove('is-visible');
	}

	// Back to top inside modal scroller
	if (scrollArea && topBtn) {
		scrollArea.addEventListener('scroll', function() {
			topBtn.classList.toggle('is-visible', scrollArea.scrollTop > 220);
		}, { passive: true });

		topBtn.addEventListener('click', function() {
			scrollArea.scrollTo({
				top: 0,
				behavior: 'smooth'
			});
		});
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

	// Lightbox close button
	if (lightboxClose) {
		lightboxClose.addEventListener('click', closeLightbox);
	}

	// Lightbox backdrop click closes
	if (lightboxOverlay) {
		lightboxOverlay.addEventListener('click', function(e) {
			if (e.target === lightboxOverlay || e.target.id === 'rs-lightbox-wrap' || e.target === lightboxImg) {
				closeLightbox();
			}
		});
	}

	// Backdrop click closes
	if (overlay) {
		overlay.addEventListener('mousedown', function(e) {
			if (e.target === overlay) {
				closeCaseStudy();
			}
		});
	}

	// ESC key closes (closes Lightbox first if open, else Case Study modal)
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' || e.key === 'Esc') {
			if (lightboxOverlay && !lightboxOverlay.hidden) {
				e.preventDefault();
				closeLightbox();
			} else if (overlay && !overlay.hidden) {
				e.preventDefault();
				closeCaseStudy();
			}
		}
	});

})();
</script>

<?php
get_footer();
