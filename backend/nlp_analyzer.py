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
    
    def analyze_sentiment(self, text: str) -> Dict:
        """
        Perform sentiment analysis on text
        Returns: {'sentiment', 'score', 'confidence'}
        """
        if not text or not isinstance(text, str):
            return {'sentiment': 'unknown', 'score': 0, 'confidence': 0}
        
        scores = self.sia.polarity_scores(text)
        compound = scores['compound']
        
        if compound >= self.sentiment_thresholds['positive']:
            sentiment = 'positive'
        elif compound <= self.sentiment_thresholds['negative']:
            sentiment = 'negative'
        else:
            sentiment = 'neutral'
        
        return {
            'sentiment': sentiment,
            'score': round(compound, 3),
            'confidence': round(max(scores['pos'], scores['neg'], scores['neu']), 3),
            'detailed_scores': {
                'positive': round(scores['pos'], 3),
                'negative': round(scores['neg'], 3),
                'neutral': round(scores['neu'], 3)
            }
        }
    
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
        Extract aspect terms (what is being reviewed about)
        Examples: 'service', 'price', 'location', 'quality', 'cleanliness'
        """
        aspect_keywords = {
            'service': ['service', 'staff', 'attendant', 'waiter', 'worker', 'employee', 'help', 'assistant'],
            'price': ['price', 'cost', 'expensive', 'cheap', 'affordable', 'value', 'fee', 'charge'],
            'quality': ['quality', 'product', 'item', 'material', 'durability', 'condition', 'taste'],
            'location': ['location', 'place', 'area', 'distance', 'accessibility', 'spot', 'position'],
            'cleanliness': ['clean', 'dirty', 'hygiene', 'sanitation', 'neat', 'tidy', 'messy'],
            'atmosphere': ['atmosphere', 'ambiance', 'environment', 'vibe', 'setting', 'decor', 'design'],
            'speed': ['fast', 'slow', 'quick', 'waiting', 'time', 'quick', 'rapid', 'prompt'],
            'comfort': ['comfort', 'comfortable', 'cozy', 'relaxing', 'convenient', 'ease']
        }
        
        text_lower = text.lower()
        detected_aspects = []
        
        for aspect, keywords in aspect_keywords.items():
            if any(keyword in text_lower for keyword in keywords):
                detected_aspects.append(aspect)
        
        return list(set(detected_aspects))
    
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
        Analyze multiple feedback items and generate overall insights
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
            'detailed_analysis': []
        }
        
        sentiment_scores = []
        all_keywords = Counter()
        all_aspects = Counter()
        
        for feedback in feedback_list:
            message = feedback.get('message', '')
            rating = int(feedback.get('rating', 3))
            
            # Sentiment analysis
            sentiment_data = self.analyze_sentiment(message)
            results['sentiment_distribution'][sentiment_data['sentiment']] += 1
            sentiment_scores.append(sentiment_data['score'])
            
            # Keyword extraction
            keywords = self.extract_keywords(message, top_n=3)
            for kw in keywords:
                all_keywords[kw['keyword']] += kw['frequency']
            
            # Aspect extraction
            aspects = self.extract_aspects(message)
            for aspect in aspects:
                all_aspects[aspect] += 1
            
            # Detailed analysis
            results['detailed_analysis'].append({
                'id': feedback.get('id'),
                'message': message,
                'rating': rating,
                'sentiment': sentiment_data['sentiment'],
                'sentiment_score': sentiment_data['score'],
                'keywords': keywords,
                'aspects': aspects,
                'summary': self.summarize_text(message, num_sentences=1)
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
        results['aspect_mentions'] = dict(all_aspects.most_common(8))
        
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
            result = analyzer.analyze_sentiment(input_data.get('text', ''))
        elif action == 'extract_keywords':
            result = analyzer.extract_keywords(
                input_data.get('text', ''),
                input_data.get('top_n', 5)
            )
        elif action == 'extract_aspects':
            result = analyzer.extract_aspects(input_data.get('text', ''))
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
