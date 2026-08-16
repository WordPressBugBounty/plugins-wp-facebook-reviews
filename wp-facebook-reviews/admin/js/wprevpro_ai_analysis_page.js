(function($){
// Simple HTML escaper available to all helpers in this file
function aiEscapeHTML(s){ return (s==null)?'':String(s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c];}); }
	$(document).ready(function(){
		// Load sample report on page open (free version - always show sample)
		(function loadLatest(){
			var data = { action: 'wprevpro_ai_get_latest_report', wpfb_nonce: adminjs_script_vars.wpfb_nonce };
			$.post(adminjs_script_vars.ajax_url, data).then(function(resp){
				if(resp && resp.success && resp.data){
					var r = resp.data;
					if(r.report_markdown){ $('#ai_report_markdown').text(r.report_markdown); }
					if(r.report_json){ $('#ai_report_json').val(JSON.stringify(r.report_json, null, 2)); renderAIReport(r.report_json); }
					setEmptyState(false);
				}
				else {
					// Fallback: still show sample even if AJAX fails
					setEmptyState(false);
				}
			}).fail(function(){
				// On error, still show sample
				setEmptyState(false);
			});
		})();

		// Filter options loading disabled in free version
		// (function loadFilterOptions(){ ... })();

		// Filter options, date picker, and create analysis functions removed in free version
		// Saved reports dropdown disabled (always show sample)

        function loadSavedById(id){
            var data = { action: 'wprevpro_ai_get_report', wpfb_nonce: adminjs_script_vars.wpfb_nonce, id: id };
            $.post(adminjs_script_vars.ajax_url, data).then(function(resp){
                if(resp && resp.success && resp.data){
					var r = resp.data;
					if(r.report_markdown){ var md = r.report_markdown; if (typeof md !== 'string') { try { md = JSON.stringify(md); } catch(e) { md = String(md); } } $('#ai_report_markdown').text(md); }
					if(r.report_json){ $('#ai_report_json').val(JSON.stringify(r.report_json, null, 2)); renderAIReport(r.report_json); }
					// track current report id
					window._aiCurrentReportId = String(id);
                }
            });
        }

        $('#ai_saved_reports').on('change', function(){
            var id = $(this).val();
            if(!id) return;
            loadSavedById(id);
        });

		$('#ai_export_json').on('click', function(){
			var content = $('#ai_report_json').val() || '{}';
			var blob = new Blob([content], {type: 'application/json'});
			var url = URL.createObjectURL(blob);
			var a = document.createElement('a');
			a.href = url; a.download = 'ai-analysis.json'; a.click();
			URL.revokeObjectURL(url);
		});

		$('#ai_export_md').on('click', function(){
			// Build a lightweight markdown from the rendered page (cards + readable report)
			var parts = [];
			// Themes
			$('#ai_report_cards .ai-card').each(function(){
				var title = $(this).find('.ai-card-title').first().text().trim();
				var body = $(this).find('.ai-card-body').first();
				if(!title) return;
				parts.push('## '+ title);
				// lists
				var list = [];
				body.find('ul.ai-list > li').each(function(){
					list.push('- '+ $(this).text().trim());
				});
				if(list.length){ parts = parts.concat(list); }
				// paragraphs
				var paras = body.clone();
				paras.find('ul,ol,.ai-quote-more,.ai-readmore').remove();
				var text = paras.text().trim();
				if(text){ parts.push(text); }
			});
			// Readable report paragraph
			var readable = $('#ai_report_markdown').text().trim();
			if(readable){ parts.push('## Readable Report'); parts.push(readable); }
			var content = parts.join('\n\n');
			var blob = new Blob([content], {type: 'text/markdown'});
			var url = URL.createObjectURL(blob);
			var a = document.createElement('a');
			a.href = url; a.download = 'ai-analysis-full.md'; a.click();
			URL.revokeObjectURL(url);
		});

	$('#ai_toggle_json').on('click', function(){
		var $c = $('#ai_json_container');
		var isHidden = ($c.css('display') === 'none');
		$c.toggle(isHidden);
		$('#ai_toggle_json').text(isHidden ? 'Hide JSON' : 'Show JSON');
	});

	// Delete analysis handler removed in free version (sample cannot be deleted)

	function renderAIReport(report){
			var html = [];
			var uid = 0;
			/*
			if(report && report.metrics && report.metrics.counts){
				html.push('<div class="ai-kpis">' +
					'<div class="ai-kpi"><div class="ai-kpi-label">Total Reviews</div><div class="ai-kpi-value">'+ (report.metrics.counts.total_reviews||0) +'</div></div>'+
					'<div class="ai-kpi"><div class="ai-kpi-label">Avg Rating</div><div class="ai-kpi-value">'+ (report.metrics.avg_rating||'—') +'</div></div>'+
				'</div>');
			}*/

			if(report && report.summary){
				html.push(card('Summary','<div>'+ escapeHTML(report.summary) +'</div>'));
			}

			// Sentiment Over Time chart if timeline points are present
			try{
				if (report.metrics && Array.isArray(report.metrics.timeline)){
					var t = report.metrics.timeline;
					var labels = t.map(function(p){ return p.date || p.period || ''; });
					var pos = t.map(function(p){ return p.positive!=null? Number(p.positive): null; });
					var neu = t.map(function(p){ return p.neutral!=null? Number(p.neutral): null; });
					var neg = t.map(function(p){ return p.negative!=null? Number(p.negative): null; });
					var ctx = document.getElementById('ai_sentiment_timeline');
					if (ctx && typeof Chart !== 'undefined'){
						var aiChart = new Chart(ctx, {
							type: 'line',
							data: { labels: labels, datasets: [
								{ label: 'Positive', data: pos, borderColor: '#2ecc71', backgroundColor: 'rgba(46,204,113,.15)', fill: true, lineTension: .2, spanGaps: true },
								{ label: 'Neutral', data: neu, borderColor: '#95a5a6', backgroundColor: 'rgba(149,165,166,.15)', fill: true, lineTension: .2, spanGaps: true },
								{ label: 'Negative', data: neg, borderColor: '#e74c3c', backgroundColor: 'rgba(231,76,60,.15)', fill: true, lineTension: .2, spanGaps: true }
							] },
							options: { responsive: true, maintainAspectRatio: true, aspectRatio: 3, plugins: { legend: { position: 'bottom' } }, scales: { yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }] } }
						});
						// Click handler: open Thickbox and fetch matching reviews by date
						$('#ai_sentiment_timeline').off('click').on('click', function(e){
							var points = aiChart.getElementsAtEvent(e);
							if(points && points.length>0){
								var idx = points[0]._index;
								var dateKey = labels[idx];
								if(!dateKey){ return; }
								var url = "#TB_inline?width=auto&height=auto&inlineId=tb_content_popup";
								tb_show(dateKey || 'Reviews', url);
								$("#TB_window").css({ "width":"80%","margin-left": "-40%","height":"80vh","top":"300px" });
								$("#TB_ajaxContent").css({ "width":"auto","height":"auto","max-height":"62vh","overflow":"auto" });
								$("#TB_window").focus();
								$("#review_details").hide();
								$("#review_list").show();
								// use last loaded report id if present on page state
								var rid = $('#ai_saved_reports').val() || window._aiCurrentReportId || '';
								var data = { action: 'wprevpro_ai_reviews_by_date', wpfb_nonce: adminjs_script_vars.wpfb_nonce, report_id: rid, date: dateKey };
								$.post(adminjs_script_vars.ajax_url, data).then(function(resp){
									var reviewshtml = '';
									if(resp && resp.success && resp.data && Array.isArray(resp.data.reviews)){
										resp.data.reviews.forEach(function(value){ reviewshtml += getreviewshtml(value); });
									}
									$("#review_list_body").html(reviewshtml);
								});
							}
						});
					}
				}
			} catch(e){}
			
			if(report.metrics){
				var m = report.metrics;
				var total = m.counts && (m.counts.total_reviews||0) || 0;
				var pos = m.counts && (m.counts.positive||0) || 0;
				var neu = m.counts && (m.counts.neutral||0) || 0;
				var neg = m.counts && (m.counts.negative||0) || 0;
				var metricsHtml = '<div class="ai-kpis">' +
					'<div class="ai-kpi"><div class="ai-kpi-label">Total Reviews</div><div class="ai-kpi-value">'+ total +'</div></div>'+
					'<div class="ai-kpi"><div class="ai-kpi-label">Avg Rating</div><div class="ai-kpi-value">'+ (typeof m.avg_rating!=='undefined' && m.avg_rating!==null ? m.avg_rating : '—') +'</div></div>'+
					'<div class="ai-kpi"><div class="ai-kpi-label">Positive</div><div class="ai-kpi-value kpi-positive">'+ pos +'</div></div>'+
					'<div class="ai-kpi"><div class="ai-kpi-label">Neutral</div><div class="ai-kpi-value kpi-neutral">'+ neu +'</div></div>'+
					'<div class="ai-kpi"><div class="ai-kpi-label">Negative</div><div class="ai-kpi-value kpi-negative">'+ neg +'</div></div>'+
				'</div>';
				html.push(card('Metrics', metricsHtml));
			}



			if(Array.isArray(report.themes) && report.themes.length){
				var lis = report.themes.map(function(t){
					var quotes = Array.isArray(t.sample_quotes)? t.sample_quotes : [];
					var first = quotes.length? truncate(quotes[0], 140) : '';
					var moreId = 'ai-more-t-'+(uid++);
					var moreHtml = '';
					if(quotes.length>1){
						moreHtml = '<div id="'+moreId+'" class="ai-quote-more" style="display:none">'+ quotes.slice(1).map(function(q){return '<div class="ai-quote">'+ escapeHTML(truncate(q, 240)) +'</div>';}).join('') + '</div>'+
							'<a href="#" class="ai-readmore" data-target="'+moreId+'">Read more</a>';
					}
					return '<li class="ai-flexbetween"><div><span class="ai-chip ai-chip--info">'+ (t.count||0) +'</span> '+ escapeHTML(t.name||'') +'</div>'+
						'<div class="ai-rightcol">'+ (first?('<div class="ai-quote">'+ escapeHTML(first) +'</div>'):'') + moreHtml + '</div></li>';
				}).join('');
				html.push(card('Themes','<ul class="ai-list ai-twocol">'+ lis +'</ul>'));
			}

			if(Array.isArray(report.pain_points) && report.pain_points.length){
				var lis2 = report.pain_points.map(function(p){
					var sev = (p.severity||'').toLowerCase();
					var examples = Array.isArray(p.examples)? p.examples : [];
					var first = examples.length? truncate(examples[0], 140) : '';
					var moreId = 'ai-more-p-'+(uid++);
					var moreHtml = '';
					if(examples.length>1){
						moreHtml = '<div id="'+moreId+'" class="ai-quote-more" style="display:none">'+ examples.slice(1).map(function(q){return '<div class="ai-quote">'+ escapeHTML(truncate(q, 240)) +'</div>';}).join('') + '</div>'+
							'<a href="#" class="ai-readmore" data-target="'+moreId+'">Read more</a>';
					}
					return '<li class="ai-flexbetween"><div><span class="ai-chip ai-chip--'+ sev +'">'+ escapeHTML(p.severity||'') +'</span> '+ escapeHTML(p.issue||'') +'</div>'+
						'<div class="ai-rightcol">'+ (first?('<div class="ai-quote">'+ escapeHTML(first) +'</div>'):'') + moreHtml + '</div></li>';
				}).join('');
				html.push(card('Pain Points','<ul class="ai-list ai-twocol">'+ lis2 +'</ul>'));
			}

			if(report && report.swot){
				html.push('<div class="ai-card"><div class="ai-card-title">SWOT</div><div class="ai-grid ai-grid--2cols">'
					+ tile('Strengths', report.swot.strengths)
					+ tile('Weaknesses', report.swot.weaknesses)
					+ tile('Opportunities', report.swot.opportunities)
					+ tile('Threats', report.swot.threats)
					+ '</div></div>');
			}

			if(Array.isArray(report.recommendations) && report.recommendations.length){
				var lis3 = report.recommendations.map(function(r){
					// Support both string and object recommendations
					if (typeof r === 'string') {
						return '<li>'+ escapeHTML(r) +'</li>';
					}
					var title = r.title || r.text || '';
					var tags = '';
					if(r.impact) tags += '<span class="ai-chip ai-chip--tag">Impact: '+ escapeHTML(r.impact) +'</span>';
					if(r.effort) tags += '<span class="ai-chip ai-chip--tag">Effort: '+ escapeHTML(r.effort) +'</span>';
					var body = title ? '<div class="ai-rec-row"><div class="ai-rec-title">'+ escapeHTML(title) +'</div><div class="ai-rec-body"><div class="ai-rec-tags">'+ tags +'</div></div></div>' : '';
					if(!body && tags){ body = '<div class="ai-rec-row"><div class="ai-rec-tags">'+ tags +'</div></div>'; }
					if(r.rationale){ body += '<div class="ai-rec-note">'+ escapeHTML(r.rationale) +'</div>'; }
					return '<li>'+ (body || escapeHTML(JSON.stringify(r))) +'</li>';
				}).join('');
				html.push(card('Recommendations','<ul class="ai-list">'+ lis3 +'</ul>'));
			} else if(report.recommendations){
				html.push(card('Recommendations','<div class="ai-empty">No recommendations provided.</div>'));
			}

			if(Array.isArray(report.personas) && report.personas.length){
				var lis4 = report.personas.map(function(pe){
					var type = pe.type || pe.label || 'Persona';
					var desc = pe.description || (Array.isArray(pe.traits) ? pe.traits.join(', ') : '');
					return '<li><strong>'+ escapeHTML(type) +':</strong> '+ escapeHTML(desc) +'</li>';
				}).join('');
				html.push(card('Personas','<ul class="ai-list">'+ lis4 +'</ul>'));
			}

			if(Array.isArray(report.faqs) && report.faqs.length){
				var lis5 = report.faqs.map(function(f){return '<li><strong>'+ escapeHTML(f.question||'Q') +':</strong> '+ escapeHTML(f.answer||'') +'</li>';}).join('');
				html.push(card('FAQs','<ul class="ai-list">'+ lis5 +'</ul>'));
			}


			if(report.notes){
				html.push(card('Notes','<div>'+ escapeHTML(report.notes) +'</div>'));
			}

			// Review growth section (summary + tactics)
			if(report.review_growth){
				var rg = report.review_growth;
				var body = '';
				if (rg.summary){ body += '<p>'+ escapeHTML(rg.summary) +'</p>'; }
				if (Array.isArray(rg.tactics) && rg.tactics.length){
					var tlist = rg.tactics.map(function(t){
						var line = '<div class="ai-rec-row"><div class="ai-rec-title">'+ escapeHTML(t.title||'Tactic') +'</div><div class="ai-rec-body">';
						if (t.rationale){ line += '<div class="ai-rec-note">'+ escapeHTML(t.rationale) +'</div>'; }
						if (Array.isArray(t.steps) && t.steps.length){
							line += '<ol class="ai-steps">'+ t.steps.map(function(s){ return '<li>'+ escapeHTML(s) +'</li>'; }).join('') +'</ol>';
						}
						line += '</div></div>';
						return '<li>'+ line +'</li>';
					}).join('');
					body += '<ul class="ai-list">'+ tlist +'</ul>';
				}
				if (body){ html.push(card('How to Grow Reviews', body)); }
			}

			$('#ai_report_cards').html(html.join(''));

			function card(title, inner){ return '<div class="ai-card"><div class="ai-card-title">'+ title +'</div><div class="ai-card-body">'+ inner +'</div></div>'; }
			function tile(title, items){
				if(!Array.isArray(items) || !items.length){ return '<div class="ai-tile"><div class="ai-tile-title">'+ title +'</div><div class="ai-tile-body ai-empty">—</div></div>'; }
				var lis = items.map(function(x){ return '<li>'+ escapeHTML(x) +'</li>'; }).join('');
				return '<div class="ai-tile"><div class="ai-tile-title">'+ title +'</div><div class="ai-tile-body"><ul class="ai-list">'+ lis +'</ul></div></div>';
			}
			function escapeHTML(s){ return (s==null)?'':String(s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c];}); }
			function truncate(s,n){ if(!s) return ''; s=String(s); return s.length>n? s.slice(0,n-1)+'…' : s; }
		}

		// Helpers to render review rows in Thickbox (mirrors analytics page behavior)
		function wprev_prourldecode(url) {
		  return decodeURIComponent(String(url||'').replace(/\+/g, ' '));
		}
		function getreviewshtml(value, searchword){
			var reviewshtml='';
			var userpic="";
			if(value.userpiclocal && value.userpiclocal!=""){
				userpic = '<img wprevid="'+aiEscapeHTML(value.id)+'" class="imgprofilepic" style="-webkit-user-select: none;width: 50px;" src="'+aiEscapeHTML(value.userpiclocal)+'">';
			} else {
				userpic = '<img wprevid="'+aiEscapeHTML(value.id)+'" class="imgprofilepic" style="-webkit-user-select: none;width: 50px;" src="'+aiEscapeHTML(value.userpic||'')+'">';
			}
			var fromname = '';
			if(value.from_name){ fromname ='-'+aiEscapeHTML(value.from_name); }
			var fromurllink = wprev_prourldecode(value.from_url||'');
			var fromurlrevlink = wprev_prourldecode(value.from_url_review||'');
			if(fromurlrevlink==''){ fromurlrevlink = fromurllink; }
			// facebook fallback
			if(value.type=='Facebook' && fromurllink==''){
				fromurllink = "https://www.facebook.com/pg/"+(value.pageid||'')+"/reviews/";
			}
			if(value.type=='Facebook' && fromurlrevlink==''){
				fromurlrevlink = 'https://www.facebook.com/search/top/?q='+encodeURI(value.reviewer_name||'');
			}
			var reviewtext = value.review_text||'';
			if(searchword && reviewtext){
				var boldsearchword = "<b>"+aiEscapeHTML(searchword)+"</b>";
				try{
					var re = new RegExp(searchword, "gi");
					reviewtext = String(reviewtext).replace(re, boldsearchword);
				}catch(e){}
			}
			if(value.owner_response){
				try{ var ownerres = JSON.parse(value.owner_response); if(ownerres && ownerres.comment){ reviewtext = reviewtext + '<div class="wppro_owners_res_div"><div class="wppro_revres_title">Review Response:</div><div>'+aiEscapeHTML(ownerres.name||'')+' - '+aiEscapeHTML(ownerres.date||'')+'</div><div>'+aiEscapeHTML(ownerres.comment||'')+'</div></div>'; } }catch(e){}
			}
			// Unescape common escapes in pagename for display (e.g., Tortora\'s -> Tortora's)
			var displayPageName = String(value.pagename||'').replace(/\\'/g, "'").replace(/\\\"/g, '"');
			reviewshtml += '<tr id="'+aiEscapeHTML(value.id)+'" rtype="'+aiEscapeHTML(value.type||'')+'">\	\
				<th scope="col" class="wprev_row_userpic wprev_row_reviewer_name manage-column"><a href="'+aiEscapeHTML(fromurlrevlink)+'" target="_blank">'+userpic+'<br>'+aiEscapeHTML(value.reviewer_name||'')+'</a></th>\	\
				<th scope="col" class="wprev_row_rating manage-column"><b>'+aiEscapeHTML(value.rating||'')+'</br>'+aiEscapeHTML(value.recommendation_type||'')+'</b></th>\	\
				<th scope="col" rtitle="'+aiEscapeHTML(value.reviewer_name||'')+'" class="wprev_row_review_text manage-column"><span class="wprev_row_review_title_span">'+aiEscapeHTML(value.review_title||'')+'</span><span class="wprev_row_review_text_span">'+reviewtext+'</span></th>\	\
				<th scope="col" class="wprev_row_created_time manage-column">'+aiEscapeHTML(value.created_time||'')+'</th>\	\
				<th scope="col" class="manage-column">'+aiEscapeHTML(value.review_length||'')+'<br>'+aiEscapeHTML(value.review_length_char||'')+'<br>'+aiEscapeHTML(value.language_code||'')+'</th>\	\
				<th scope="col" class="manage-column">'+aiEscapeHTML(displayPageName)+'</br><a href="'+aiEscapeHTML(fromurllink)+'" target="_blank">'+aiEscapeHTML(value.type||'')+fromname+'</a></th></tr>';
			return reviewshtml;
		}

		$('#ai_report_cards').on('click','.ai-readmore', function(e){
			e.preventDefault();
			var id = $(this).data('target');
			var $t = $('#'+id);
			var show = !$t.is(':visible');
			$t.toggle(show);
			$(this).text(show ? 'Read less' : 'Read more');
		});

		function setEmptyStateNoForm(isEmpty){
			if(isEmpty){
				$('#ai_saved_reports, #ai_load_saved').hide();
				$('#ai_report_cards').closest('.w3-row').hide();
				$('#ai_report_markdown').closest('.w3-row').hide();
				$('#ai_json_container').closest('.w3-row').hide();
				$('.sentiment_over_time_div').hide();
			} else {
				$('#ai_saved_reports, #ai_load_saved').show();
				$('#ai_report_cards').closest('.w3-row').show();
				$('#ai_report_markdown').closest('.w3-row').show();
				$('#ai_json_container').closest('.w3-row').show();
				$('.sentiment_over_time_div').show();
			}
		}
		// Hide everything except the Generate button and header controls when no report exists
		function setEmptyState(isEmpty){
			if(isEmpty){
				$('#ai_saved_reports, #ai_load_saved').hide();
				$('#ai_report_cards').closest('.w3-row').hide();
				$('#ai_report_markdown').closest('.w3-row').hide();
				$('#ai_json_container').closest('.w3-row').hide();
				$('.sentiment_over_time_div').hide();
				// Ensure the new analysis form remains hidden until user opens it
				$('.ai_new_analysis_form').hide();
			} else {
				$('#ai_saved_reports, #ai_load_saved').show();
				$('#ai_report_cards').closest('.w3-row').show();
				$('#ai_report_markdown').closest('.w3-row').show();
				$('#ai_json_container').closest('.w3-row').show();
				$('.sentiment_over_time_div').show();
			}
		}
	});
})(jQuery);


