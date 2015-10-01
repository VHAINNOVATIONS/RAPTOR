using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Data;

namespace gov.va.medora.utils
{
    public static class DbReaderUtil
    {
        public static string getValue(IDataReader reader, int index)
        {
            return (reader[index] == DBNull.Value) ? "" : (string)reader[index];
        }

        public static bool getBooleanFromYNValue(IDataReader reader, int index)
        {
            if (reader[index] == DBNull.Value) return false;

            if("Y".Equals((string)reader[index])) 
            {
                return true;
            } 
            else 
            {
                return false;
            }
        }

        public static string getInt16Value(IDataReader reader, int index)
        {
            return (reader[index] == DBNull.Value) ? "" : ((Int16)reader[index]).ToString();
        }

        public static string getInt32Value(IDataReader reader, int index)
        {
            return (reader[index] == DBNull.Value) ? "" : ((Int32)reader[index]).ToString();
        }

        public static string getInt64Value(IDataReader reader, int index)
        {
            return (reader[index] == DBNull.Value) ? "" : ((Int64)reader[index]).ToString();
        }

        public static string getNumericValue(IDataReader reader, int index)
        {
            return (reader[index] == DBNull.Value) ? "" : ((Double)reader[index]).ToString();
        }

        public static string getDateValue(IDataReader reader, int index)
        {
            return (reader[index] == DBNull.Value) ? "" : ((DateTime)reader[index]).ToShortDateString();
        }

        public static string getDateTimeValue(IDataReader reader, int index)
        {
            return (reader[index] == DBNull.Value) ? "" : ((DateTime)reader[index]).ToString();
        }
    }
}
