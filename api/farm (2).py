#!/usr/bin/env python
# coding: utf-8

# In[72]:


import numpy as np
import pandas as pd
import seaborn as sns
import pickle

# Read the CSV file
df = pd.read_csv('farm_data.csv')


# In[73]:


df


# In[74]:


df = df.drop_duplicates()


# In[75]:


df.shape


# In[76]:


df.isna().sum()


# In[77]:


df.dtypes


# In[78]:


df.shape


# In[79]:


newdf=df


# In[80]:


from sklearn.preprocessing import LabelEncoder

# Create a LabelEncoder object
le = LabelEncoder()

# Fit the LabelEncoder to the "CropName" column
le.fit(df["CropName"])

# Transform the "CropName" column using the LabelEncoder
df["CropName"] = le.transform(df["CropName"])

# save the encoder object to a pickle file
with open("crop_encoder.sav", "wb") as f:
    pickle.dump(le, f)



# In[81]:


df


# In[82]:


sns.scatterplot(x="Profit",y="SeededArea",data=newdf)


# In[83]:


from sklearn.preprocessing import StandardScaler
#MinMaxScaler

X = newdf[['CropName', 'SeededArea']]
y = newdf['Profit']


# In[84]:


scaler = StandardScaler();
X_scaled = scaler.fit_transform(X)


# In[85]:


from sklearn.model_selection import train_test_split

# split the data into training and testing sets
X_train, X_test, y_train, y_test = train_test_split(X_scaled, y, test_size=0.2, random_state=42)


# In[86]:


from sklearn.linear_model import LinearRegression
from sklearn.metrics import mean_squared_error
import matplotlib.pyplot as plt

plt.scatter(y_test, y_pred)
plt.xlabel("Actual Profit")
plt.ylabel("Predicted Profit")
plt.title("Predicted vs Actual Profit")
plt.show()


# In[87]:


from sklearn.metrics import mean_squared_error, mean_absolute_error, r2_score

mse = mean_squared_error(y_test, y_pred)
rmse = np.sqrt(mse)
mae = mean_absolute_error(y_test, y_pred)
r2 = r2_score(y_test, y_pred)

model = LinearRegression()
model.fit(X_train, y_train)
y_pred = model.predict(X_test)

print("Mean Squared Error: ", mse)
print("Root Mean Squared Error: ", rmse)
print("Mean Absolute Error: ", mae)
print("R2 score (TRAINING): ", r2_score(y_train,model.predict(X_train)));
print("R2 score (TEST): ", r2_score(y_test,model.predict(X_test)));


# In[88]:


f = open("profit_model.pkl", 'wb')
pickle.dump(model,f)
f.close()

