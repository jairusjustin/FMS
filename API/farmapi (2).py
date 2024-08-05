#!/usr/bin/env python
# coding: utf-8

# In[1]:

from flask import Flask,request,jsonify
from flask_cors import CORS, cross_origin
import pickle
import pandas as pd
import numpy as np

app = Flask(__name__);

cors = CORS(app, resource ={r"/*": {"origins": "*"}})


# In[2]:


@app.route("/api/test",methods=["POST"])
def hello():
    return "Your API is working";


@app.route("/api/predict_profit", methods=["POST"])
def predict_profit():
    data = request.get_json()

    model_path = "/home/jairusjustin/mysite/profit_model.pkl"
    encoder_path = "/home/jairusjustin/mysite/crop_encoder.sav"

    f = open(model_path , 'rb')
    model = pickle.load(f)
    f.close();

    df = pd.DataFrame(data.values())
    df = df.transpose()
    df.columns = data.keys()
    prediction = model.predict(df);


    profit_prediction = model.predict(df)


    f = open(encoder_path, 'rb')
    encoder = pickle.load(f)
    f.close()

    return jsonify({"profit_prediction": profit_prediction[0]})


# In[ ]:


if __name__ == "__main__":
    app.run()

