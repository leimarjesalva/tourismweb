"""
Legazpi Explorer - Natural Language Processing Analytics Module
Analyzes guest feedback using NLTK and spaCy
"""

import json
import sys
import re
from typing import Dict, List, Tuple
from collections import Counter

try:
    import nltk
    from nltk.sentiment import SentimentIntensityAnalyzer
    from nltk.tokenize import sent_tokenize, word_tokenize
    from nltk.corpus import stopwords
    from nltk.probability import FreqDist
    
    import spacy
except ImportError as e:
    print(json.dumps({
        'success': False,
        'error': f'Missing required NLP library: {str(e)}. Run: pip install nltk spacy scikit-learn'
    }))
    sys.exit(1)

# Download required NLTK data if not present
try:
    nltk.data.find('sentiment/vader_lexicon')
except LookupError:
    nltk.download('vader_lexicon', quiet=True)

try:
    nltk.data.find('tokenizers/punkt')
except LookupError:
    nltk.download('punkt', quiet=True)

try:
    nltk.data.find('corpora/stopwords')
except LookupError:
    nltk.download('stopwords', quiet=True)

try:
    nltk.data.find('averaged_perceptron_tagger')
except LookupError:
    nltk.download('averaged_perceptron_tagger', quiet=True)

class FeedbackAnalyzer:
    def __init__(self):
        """Initialize NLP models"""
        self.sia = SentimentIntensityAnalyzer()
        try:
            self.nlp = spacy.load("en_core_web_sm")
        except OSError:
            print(json.dumps({
                'success': False,
                'error': 'spaCy model not found. Run: python -m spacy download en_core_web_sm'
            }))
            sys.exit(1)
        
        self.stop_words = set(stopwords.words('english'))
        self.sentiment_thresholds = {
            'positive': 0.05,
            'neutral': (-0.05, 0.05),
            'negative': -0.05
        }

        # Tourism-specific sentiment lexicon for VADER
        tourism_lexicon = {
            'breathtaking': 3.2, 'stunning': 3.0, 'majestic': 2.8, 'picturesque': 2.5,
            'paradise': 3.5, 'gem': 2.3, 'must-visit': 3.0, 'must visit': 3.0,
            'instagrammable': 2.0, 'scenic': 2.5, 'pristine': 2.4, 'serene': 2.2,
            'world-class': 3.0, 'authentic': 2.0, 'unforgettable': 3.0, 'magical': 2.8,
            'amazing view': 3.2, 'great view': 2.8, 'best experience': 3.0,
            'worth it': 2.5, 'worth the trip': 2.8, 'highly recommend': 3.0,
            'overpriced': -2.5, 'overcrowded': -2.0, 'scam': -3.5, 'rip-off': -3.0,
            'ripoff': -3.0, 'tourist trap': -2.8, 'disappointing': -2.5, 'underwhelming': -2.0,
            'sketchy': -2.2, 'unsafe': -3.0, 'dangerous': -3.0, 'filthy': -3.0,
            'stinky': -2.5, 'smelly': -2.3, 'rundown': -2.2, 'dilapidated': -2.5,
            'rude staff': -3.0, 'rude': -2.0, 'unfriendly': -2.2, 'unhelpful': -2.0,
            'long wait': -1.8, 'long queue': -1.8, 'crowded': -1.5, 'noisy': -1.3,
            'buggy': -1.5, 'mosquitoes': -1.2, 'hot': -0.5, 'humid': -0.5,
            'friendly': 2.2, 'hospitable': 2.5, 'welcoming': 2.3, 'accommodating': 2.0,
            'delicious': 2.8, 'tasty': 2.3, 'mouthwatering': 2.8, 'fresh': 1.8,
            'clean': 1.8, 'spotless': 2.5, 'well-maintained': 2.2, 'organized': 1.8,
            'spacious': 1.8, 'cozy': 2.0, 'comfortable': 2.0, 'convenient': 1.8,
            'affordable': 2.0, 'good value': 2.3, 'cheap': 1.0, 'budget-friendly': 2.0,
            'expensive': -1.5, 'pricey': -1.3, 'not worth': -2.5, 'waste of money': -3.0,
            'waste of time': -2.8, 'boring': -2.0, 'nothing special': -1.8,
            'mayon': 1.5, 'legazpi': 1.0, 'bicol': 1.0, 'bicolano': 1.5,
            'laing': 1.5, 'bicol express': 1.5, 'pili': 1.2, 'sili': 1.0,
        }
        self.sia.lexicon.update(tourism_lexicon)

        # Expanded aspect keywords for tourism context
        self.aspect_keywords = {
            'service': ['service', 'staff', 'attendant', 'waiter', 'worker', 'employee', 'help',
                        'assistant', 'guide', 'tour guide', 'receptionist', 'crew', 'management',
                        'customer service', 'front desk', 'concierge', 'porter', 'host', 'caretaker'],
            'price': ['price', 'cost', 'expensive', 'cheap', 'affordable', 'value', 'fee', 'charge',
                      'overpriced', 'budget', 'worth', 'pricey', 'money', 'entrance fee', 'ticket',
                      'rate', 'deal', 'discount', 'free', 'bargain', 'rip-off', 'reasonable'],
            'food': ['food', 'meal', 'dish', 'restaurant', 'eat', 'drink', 'taste', 'delicious',
                     'menu', 'breakfast', 'lunch', 'dinner', 'snack', 'coffee', 'beverage',
                     'flavor', 'cuisine', 'chef', 'cook', 'serving', 'portion', 'buffet',
                     'laing', 'bicol express', 'pili', 'sili', 'spicy', 'fresh'],
            'location': ['location', 'place', 'area', 'distance', 'accessibility', 'spot', 'position',
                         'view', 'scenery', 'scenic', 'nearby', 'far', 'directions', 'road',
                         'parking', 'transportation', 'commute', 'ride', 'walk', 'hike',
                         'trail', 'route', 'path', 'accessible'],
            'cleanliness': ['clean', 'dirty', 'hygiene', 'sanitation', 'neat', 'tidy', 'messy',
                           'garbage', 'trash', 'litter', 'smell', 'odor', 'stink', 'filthy',
                           'maintained', 'restroom', 'toilet', 'bathroom', 'wash'],
            'atmosphere': ['atmosphere', 'ambiance', 'environment', 'vibe', 'setting', 'decor',
                          'design', 'aesthetic', 'mood', 'peaceful', 'quiet', 'noisy', 'loud',
                          'relaxing', 'romantic', 'cozy', 'charming', 'beautiful', 'scenic'],
            'safety': ['safe', 'unsafe', 'security', 'danger', 'risk', 'guard', 'warning',
                       'accident', 'emergency', 'theft', 'pickpocket', 'scam', 'sketchy',
                       'reliable', 'trustworthy', 'secure', 'protected'],
            'accommodation': ['room', 'hotel', 'bed', 'pillow', 'blanket', 'shower', 'wifi',
                             'internet', 'aircon', 'ac', 'check-in', 'checkout', 'reservation',
                             'booking', 'lobby', 'pool', 'gym', 'amenities', 'facilities',
                             'hometel', 'inn', 'resort', 'hostel', 'apartment'],
            'crowd': ['crowded', 'packed', 'busy', 'queue', 'line', 'wait', 'waiting',
                      'peak', 'tourist', 'crowd', 'empty', 'spacious', 'private', 'secluded'],
            'experience': ['experience', 'adventure', 'fun', 'enjoy', 'exciting', 'boring',
                          'memorable', 'unforgettable', 'amazing', 'wonderful', 'fantastic',
                          'disappointing', 'waste', 'worth', 'recommend', 'return', 'revisit']
        }

        # Emotional tone patterns
        self.emotion_patterns = {
            'joy': ['love', 'loved', 'amazing', 'wonderful', 'fantastic', 'best', 'enjoy', 'enjoyed',
                    'happy', 'great', 'excellent', 'perfect', 'beautiful', 'awesome', 'incredible',
                    'paradise', 'magical', 'breathtaking', 'stunning', 'wow'],
            'frustration': ['frustrated', 'annoying', 'annoyed', 'terrible', 'worst', 'horrible',
                           'awful', 'never again', 'waste', 'unacceptable', 'ridiculous',
                           'useless', 'pathetic', 'nightmare', 'regret'],
            'surprise': ['unexpected', 'surprising', 'didnt expect', "didn't expect", 'blown away',
                        'beyond expectations', 'exceeded', 'impressed', 'shock', 'astonished',
                        'unbelievable', 'jaw-dropping', 'wow'],
            'disappointment': ['disappointing', 'disappointed', 'letdown', 'let down', 'expected more',
                              'not worth', 'overhyped', 'overrated', 'underwhelming', 'meh',
                              'mediocre', 'nothing special', 'so-so'],
            'gratitude': ['thank', 'thanks', 'grateful', 'appreciate', 'appreciated', 'thankful',
                         'blessed', 'kudos', 'props', 'shoutout', 'shout out', 'god bless'],
            'trust': ['recommend', 'recommended', 'reliable', 'trustworthy', 'dependable',
                     'consistent', 'always', 'never disappoints', 'go-to', 'favorite', 'favourite']
        }
    
    def analyze_sentiment(self, text: str, rating: int = None) -> Dict:
        """
        Perform sentiment analysis on text with optional rating-aware correction.
        Combines VADER lexicon-based sentiment with rating signal for better accuracy.
        Returns: {'sentiment', 'score', 'confidence', 'emotions', 'intensity'}
        """
        if not text or not isinstance(text, str):
            return {'sentiment': 'unknown', 'score': 0, 'confidence': 0, 'emotions': [], 'intensity': 'none'}
        
        scores = self.sia.polarity_scores(text)
        compound = scores['compound']
        
        # Rating-sentiment correlation: adjust when text sentiment contradicts star rating
        if rating is not None:
            rating = int(rating)
            rating_sentiment = (rating - 3) / 2.0  # Maps 1-5 to -1.0 to 1.0
            # Blend text sentiment (70%) with rating signal (30%) for short texts
            word_count = len(text.split())
            if word_count < 8:
                # Short reviews: rating is more reliable than text sentiment
                compound = compound * 0.5 + rating_sentiment * 0.5
            elif word_count < 20:
                compound = compound * 0.7 + rating_sentiment * 0.3
            else:
                compound = compound * 0.85 + rating_sentiment * 0.15
        
        if compound >= self.sentiment_thresholds['positive']:
            sentiment = 'positive'
        elif compound <= self.sentiment_thresholds['negative']:
            sentiment = 'negative'
        else:
            sentiment = 'neutral'
        
        # Detect emotional tones
        emotions = self._detect_emotions(text)
        
        # Intensity classification
        abs_score = abs(compound)
        if abs_score >= 0.6:
            intensity = 'strong'
        elif abs_score >= 0.3:
            intensity = 'moderate'
        elif abs_score >= 0.05:
            intensity = 'mild'
        else:
            intensity = 'neutral'
        
        return {
            'sentiment': sentiment,
            'score': round(compound, 3),
            'confidence': round(max(scores['pos'], scores['neg'], scores['neu']), 3),
            'detailed_scores': {
                'positive': round(scores['pos'], 3),
                'negative': round(scores['neg'], 3),
                'neutral': round(scores['neu'], 3)
            },
            'emotions': emotions,
            'intensity': intensity
        }
    
    def _detect_emotions(self, text: str) -> List[str]:
        """Detect emotional tones in text"""
        text_lower = text.lower()
        detected = []
        for emotion, patterns in self.emotion_patterns.items():
            if any(p in text_lower for p in patterns):
                detected.append(emotion)
        return detected
    
    def extract_keywords(self, text: str, top_n: int = 5) -> List[Dict]:
        """
        Extract important keywords/entities from text
        Returns: List of {'keyword', 'type', 'frequency'}
        """
        if not text or not isinstance(text, str):
            return []
        
        keywords = []
        
        # Extract named entities
        doc = self.nlp(text.lower())
        entities = {}
        for ent in doc.ents:
            if ent.label_ in ['PERSON', 'ORG', 'GPE', 'PRODUCT']:
                key = ent.text
                entities[key] = entities.get(key, 0) + 1
        
        # Extract noun phrases and important terms
        nouns = {}
        for token in doc:
            if token.pos_ in ['NOUN', 'PROPN'] and not token.is_stop:
                word = token.text.lower()
                if len(word) > 2:
                    nouns[word] = nouns.get(word, 0) + 1
        
        # Combine and sort
        all_terms = {**entities, **nouns}
        sorted_terms = sorted(all_terms.items(), key=lambda x: x[1], reverse=True)
        
        keywords = [
            {
                'keyword': term,
                'frequency': freq,
                'type': 'entity' if term in entities else 'term'
            }
            for term, freq in sorted_terms[:top_n]
        ]
        
        return keywords
    
    def extract_aspects(self, text: str) -> List[str]:
        """
        Extract aspect terms from text using expanded tourism-specific categories.
        """
        text_lower = text.lower()
        detected_aspects = []
        
        for aspect, keywords in self.aspect_keywords.items():
            if any(keyword in text_lower for keyword in keywords):
                detected_aspects.append(aspect)
        
        return list(set(detected_aspects))
    
    def extract_aspect_sentiments(self, text: str) -> Dict[str, Dict]:
        """
        Aspect-based sentiment analysis: determine sentiment PER aspect mentioned.
        Returns: {'service': {'sentiment': 'positive', 'score': 0.8}, ...}
        """
        if not text or not isinstance(text, str):
            return {}
        
        sentences = sent_tokenize(text)
        aspect_sentiments = {}
        
        for aspect, keywords in self.aspect_keywords.items():
            # Find sentences mentioning this aspect
            relevant_sents = []
            for sent in sentences:
                sent_lower = sent.lower()
                if any(kw in sent_lower for kw in keywords):
                    relevant_sents.append(sent)
            
            if relevant_sents:
                combined = ' '.join(relevant_sents)
                scores = self.sia.polarity_scores(combined)
                compound = scores['compound']
                if compound >= 0.05:
                    sent_label = 'positive'
                elif compound <= -0.05:
                    sent_label = 'negative'
                else:
                    sent_label = 'neutral'
                
                aspect_sentiments[aspect] = {
                    'sentiment': sent_label,
                    'score': round(compound, 3),
                    'mentions': len(relevant_sents)
                }
        
        return aspect_sentiments
    
    def generate_improvement_suggestions(self, feedback_list: List[Dict]) -> List[Dict]:
        """
        Analyze negative feedback patterns and generate actionable improvement suggestions.
        """
        negative_aspects = Counter()
        negative_messages = {}
        
        for fb in feedback_list:
            msg = fb.get('message', '')
            rating = int(fb.get('rating', 3))
            if rating <= 2 or self.sia.polarity_scores(msg)['compound'] <= -0.2:
                aspects = self.extract_aspects(msg)
                for a in aspects:
                    negative_aspects[a] += 1
                    if a not in negative_messages:
                        negative_messages[a] = []
                    negative_messages[a].append(msg[:120])
        
        suggestions = []
        suggestion_templates = {
            'service': 'Guests frequently mention service issues. Consider staff training on hospitality and guest interaction.',
            'price': 'Pricing concerns are common. Review pricing strategy — consider transparent pricing or value-added packages.',
            'food': 'Food quality appears in negative feedback. Review menu quality, freshness, and consider guest dietary preferences.',
            'cleanliness': 'Cleanliness is a recurring concern. Implement stricter cleaning schedules and regular inspections.',
            'location': 'Location/access issues mentioned. Improve signage, provide clear directions, or offer shuttle services.',
            'atmosphere': 'Atmosphere concerns noted. Review ambiance, noise levels, and overall environment design.',
            'safety': 'Safety concerns raised by guests. Review security measures, lighting, and emergency protocols.',
            'accommodation': 'Room/accommodation issues reported. Inspect facilities regularly and address maintenance promptly.',
            'crowd': 'Crowd management issues noted. Consider timed entry, capacity limits, or off-peak incentives.',
            'experience': 'Overall experience needs improvement. Review the end-to-end guest journey for pain points.'
        }
        
        for aspect, count in negative_aspects.most_common(5):
            if count >= 1:
                suggestions.append({
                    'aspect': aspect,
                    'complaint_count': count,
                    'suggestion': suggestion_templates.get(aspect, f'Review {aspect} based on guest feedback.'),
                    'sample_complaints': negative_messages.get(aspect, [])[:3],
                    'priority': 'high' if count >= 3 else 'medium' if count >= 2 else 'low'
                })
        
        return suggestions
    
    def summarize_text(self, text: str, num_sentences: int = 2) -> str:
        """
        Generate abstractive summary of text
        """
        if not text or not isinstance(text, str):
            return ""
        
        sentences = sent_tokenize(text)
        if len(sentences) <= num_sentences:
            return text
        
        # Score sentences based on keyword frequency
        words = word_tokenize(text.lower())
        word_freq = FreqDist(w for w in words if w.isalnum() and w not in self.stop_words)
        
        sentence_scores = {}
        for i, sentence in enumerate(sentences):
            sentence_words = word_tokenize(sentence.lower())
            score = sum(word_freq[word] for word in sentence_words if word in word_freq)
            sentence_scores[i] = score
        
        # Get top sentences in original order
        top_indices = sorted(
            sorted(sentence_scores.items(), key=lambda x: x[1], reverse=True)[:num_sentences],
            key=lambda x: x[0]
        )
        
        summary = ' '.join(sentences[i] for i, _ in top_indices)
        return summary
    
    def analyze_batch_feedback(self, feedback_list: List[Dict]) -> Dict:
        """
        Analyze multiple feedback items and generate comprehensive insights
        including aspect sentiments, emotion distribution, improvement suggestions,
        and rating-sentiment correlation.
        """
        if not feedback_list:
            return {
                'success': False,
                'error': 'No feedback provided'
            }
        
        results = {
            'total_feedback': len(feedback_list),
            'sentiment_distribution': {'positive': 0, 'negative': 0, 'neutral': 0},
            'average_sentiment_score': 0,
            'top_keywords': [],
            'aspect_mentions': {},
            'aspect_sentiments': {},
            'emotion_distribution': {},
            'intensity_distribution': {'strong': 0, 'moderate': 0, 'mild': 0, 'neutral': 0},
            'rating_sentiment_correlation': [],
            'improvement_suggestions': [],
            'highlights': {'best_reviews': [], 'worst_reviews': []},
            'detailed_analysis': []
        }
        
        sentiment_scores = []
        all_keywords = Counter()
        all_aspects = Counter()
        all_aspect_sentiments = {}
        all_emotions = Counter()
        
        for feedback in feedback_list:
            message = feedback.get('message', '')
            rating = int(feedback.get('rating', 3))
            
            # Sentiment analysis with rating awareness
            sentiment_data = self.analyze_sentiment(message, rating)
            results['sentiment_distribution'][sentiment_data['sentiment']] += 1
            sentiment_scores.append(sentiment_data['score'])
            
            # Intensity tracking
            results['intensity_distribution'][sentiment_data.get('intensity', 'neutral')] += 1
            
            # Emotion tracking
            for emotion in sentiment_data.get('emotions', []):
                all_emotions[emotion] += 1
            
            # Keyword extraction
            keywords = self.extract_keywords(message, top_n=3)
            for kw in keywords:
                all_keywords[kw['keyword']] += kw['frequency']
            
            # Aspect extraction
            aspects = self.extract_aspects(message)
            for aspect in aspects:
                all_aspects[aspect] += 1
            
            # Aspect-based sentiment
            aspect_sents = self.extract_aspect_sentiments(message)
            for asp, data in aspect_sents.items():
                if asp not in all_aspect_sentiments:
                    all_aspect_sentiments[asp] = {'scores': [], 'sentiments': []}
                all_aspect_sentiments[asp]['scores'].append(data['score'])
                all_aspect_sentiments[asp]['sentiments'].append(data['sentiment'])
            
            # Rating-sentiment correlation
            results['rating_sentiment_correlation'].append({
                'rating': rating,
                'sentiment_score': sentiment_data['score']
            })
            
            # Detailed analysis per item
            analysis_item = {
                'id': feedback.get('id'),
                'message': message,
                'rating': rating,
                'sentiment': sentiment_data['sentiment'],
                'sentiment_score': sentiment_data['score'],
                'intensity': sentiment_data.get('intensity', 'neutral'),
                'emotions': sentiment_data.get('emotions', []),
                'keywords': keywords,
                'aspects': aspects,
                'aspect_sentiments': aspect_sents,
                'summary': self.summarize_text(message, num_sentences=1)
            }
            results['detailed_analysis'].append(analysis_item)
            
            # Track best/worst reviews
            if rating >= 4 and sentiment_data['score'] >= 0.3:
                results['highlights']['best_reviews'].append({
                    'message': message[:200],
                    'rating': rating,
                    'score': sentiment_data['score'],
                    'user': feedback.get('user_name', 'Guest')
                })
            elif rating <= 2 or sentiment_data['score'] <= -0.3:
                results['highlights']['worst_reviews'].append({
                    'message': message[:200],
                    'rating': rating,
                    'score': sentiment_data['score'],
                    'user': feedback.get('user_name', 'Guest')
                })
        
        # Calculate overall sentiment
        if sentiment_scores:
            results['average_sentiment_score'] = round(sum(sentiment_scores) / len(sentiment_scores), 3)
        
        # Top keywords
        results['top_keywords'] = [
            {'keyword': kw, 'count': count}
            for kw, count in all_keywords.most_common(10)
        ]
        
        # Aspect mentions
        results['aspect_mentions'] = dict(all_aspects.most_common(10))
        
        # Aggregate aspect sentiments
        for asp, data in all_aspect_sentiments.items():
            avg_score = round(sum(data['scores']) / len(data['scores']), 3) if data['scores'] else 0
            pos_count = data['sentiments'].count('positive')
            neg_count = data['sentiments'].count('negative')
            neu_count = data['sentiments'].count('neutral')
            results['aspect_sentiments'][asp] = {
                'average_score': avg_score,
                'sentiment': 'positive' if avg_score >= 0.05 else ('negative' if avg_score <= -0.05 else 'neutral'),
                'distribution': {'positive': pos_count, 'negative': neg_count, 'neutral': neu_count},
                'total_mentions': len(data['scores'])
            }
        
        # Emotion distribution
        results['emotion_distribution'] = dict(all_emotions.most_common(6))
        
        # Improvement suggestions from negative feedback
        results['improvement_suggestions'] = self.generate_improvement_suggestions(feedback_list)
        
        # Trim highlights to top 3
        results['highlights']['best_reviews'] = sorted(
            results['highlights']['best_reviews'], key=lambda x: x['score'], reverse=True
        )[:3]
        results['highlights']['worst_reviews'] = sorted(
            results['highlights']['worst_reviews'], key=lambda x: x['score']
        )[:3]
        
        results['success'] = True
        return results

def main():
    """Main entry point for command-line usage"""
    try:
        # Read input from stdin
        input_data = json.loads(sys.stdin.read())
        
        analyzer = FeedbackAnalyzer()
        action = input_data.get('action', '')
        
        if action == 'analyze_sentiment':
            result = analyzer.analyze_sentiment(
                input_data.get('text', ''),
                input_data.get('rating', None)
            )
        elif action == 'extract_keywords':
            result = analyzer.extract_keywords(
                input_data.get('text', ''),
                input_data.get('top_n', 5)
            )
        elif action == 'extract_aspects':
            result = analyzer.extract_aspects(input_data.get('text', ''))
        elif action == 'extract_aspect_sentiments':
            result = analyzer.extract_aspect_sentiments(input_data.get('text', ''))
        elif action == 'summarize':
            result = analyzer.summarize_text(
                input_data.get('text', ''),
                input_data.get('num_sentences', 2)
            )
        elif action == 'analyze_batch':
            result = analyzer.analyze_batch_feedback(input_data.get('feedback', []))
        else:
            result = {'success': False, 'error': 'Unknown action'}
        
        print(json.dumps(result))
    except json.JSONDecodeError as e:
        print(json.dumps({
            'success': False,
            'error': f'Invalid JSON input: {str(e)}'
        }))
    except Exception as e:
        print(json.dumps({
            'success': False,
            'error': f'Analysis error: {str(e)}'
        }))

if __name__ == '__main__':
    main()
