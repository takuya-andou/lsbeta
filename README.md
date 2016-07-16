lsbeta
======
####Yii2 project
Currently working on it with my friends. The actual version of it is being stored at another git-repository where we are working on it, so updates are not coming out at current lsbeta repository frequenlty. This project is the most challenging one among the others I've ever dealt with. At the moment we have a poor frontend progress because of one of the members has a hard time at work.
Tasks I solved in the project:
- Data (in this case sport bets) parsing from three websites and it's futher saving and manipulation. Located at the **extensions/parsing** folder.
- Yet, because of priorities our team made and prediction model imperfection, not fully used genetical algorithm for prediction model selection. Located at the **extensions/geneticalgorithm** folder.
- Betting module implementation. Uniqueness of each bet and  moving outdated bets to history caused some difficulties which still were got over. Located at **modules/betting** folder.
- Forecast module implementation. Forecasts which users leave at the website have to be updated periodically to get to know its results according to match statistics. Users may also like/dislike a forecast. Located at **modules/forecasts** folder.
- Admin module implementation (user managing, model (for predicting match outcome)  testing and managing, forecast managing and currently working on moderation). Located at **modules/admin** folder.