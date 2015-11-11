using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Data;

namespace gov.va.medora.mdo.dao.mock
{
    public class MockDataReader : IDataReader
    {
        public void Close()
        {
            return;
        }

        public int Depth
        {
            get { throw new NotImplementedException("Not implemented in MockDataReader"); }
        }

        public DataTable GetSchemaTable()
        {
            if (this.Table == null)
            {
                this.Table = new DataTable();
            }
            return this.Table;
        }

        public bool IsClosed
        {
            get { throw new NotImplementedException("Not implemented in MockDataReader"); }
        }

        public bool NextResult()
        {
            throw new NotImplementedException("Not implemented in MockDataReader");
        }

        public bool Read()
        {
            return ++_rowIndex < _results.Rows.Count;
        }

        public int RecordsAffected
        {
            get { throw new NotImplementedException("Not implemented in MockDataReader"); }
        }

        public void Dispose()
        {
            throw new NotImplementedException("Not implemented in MockDataReader");
        }

        public int FieldCount
        {
            get { return this._adjustedColumnNameMap.Count; }
        }

        public bool GetBoolean(int i)
        {
            return Convert.ToBoolean(_results.Rows[_rowIndex][i]);
        }

        public byte GetByte(int i)
        {
            return Convert.ToByte(_results.Rows[_rowIndex][i]);
        }

        public long GetBytes(int i, long fieldOffset, byte[] buffer, int bufferoffset, int length)
        {
            return Convert.ToInt64(_results.Rows[_rowIndex][i]);
        }

        public char GetChar(int i)
        {
            return Convert.ToChar(_results.Rows[_rowIndex][i]);
        }

        public long GetChars(int i, long fieldoffset, char[] buffer, int bufferoffset, int length)
        {
            throw new NotImplementedException("Not implemented in MockDataReader");
        }

        public IDataReader GetData(int i)
        {
            throw new NotImplementedException("Not implemented in MockDataReader");
        }

        public string GetDataTypeName(int i)
        {
            throw new NotImplementedException("Not implemented in MockDataReader");
        }

        public DateTime GetDateTime(int i)
        {
            return Convert.ToDateTime(_results.Rows[_rowIndex][i]);
        }

        public decimal GetDecimal(int i)
        {
            return Convert.ToDecimal(_results.Rows[_rowIndex][i]);
        }

        public double GetDouble(int i)
        {
            return Convert.ToDouble(_results.Rows[_rowIndex][i]);
        }

        public Type GetFieldType(int i)
        {
            return (this._results.Rows[_rowIndex][i]).GetType();
        }

        public float GetFloat(int i)
        {
            return Convert.ToUInt64(_results.Rows[_rowIndex][i]);
        }

        public Guid GetGuid(int i)
        {
            return new Guid(Convert.ToString(_results.Rows[_rowIndex][i]));
        }

        public short GetInt16(int i)
        {
            return Convert.ToInt16(_results.Rows[_rowIndex][i]);
        }

        public int GetInt32(int i)
        {
            return Convert.ToInt32(_results.Rows[_rowIndex][i]);
        }

        public long GetInt64(int i)
        {
            return Convert.ToInt64(_results.Rows[_rowIndex][i]);
        }

        public string GetName(int i)
        {
            return _nonAdjustedColumnNameMap.Keys.ToArray<string>()[i]; // be sure to return non-adjusted name
        }

        public int GetOrdinal(string name)
        {
            string adjustedName = name.ToLower();
            return _adjustedColumnNameMap[adjustedName];
        }

        public string GetString(int i)
        {
            return Convert.ToString(_results.Rows[_rowIndex][i]);
        }

        public object GetValue(int i)
        {
            return _results.Rows[_rowIndex][i];
        }

        public int GetValues(object[] values)
        {
            throw new NotImplementedException("Not implemented in MockDataReader");
        }

        public bool IsDBNull(int i)
        {
            return _results.Rows[_rowIndex][i] == DBNull.Value;
        }

        public object this[string name]
        {
            get 
            {
                return _results.Rows[_rowIndex][this.GetOrdinal(name)];
            }
        }

        public object this[int i]
        {
            get 
            {
                return _results.Rows[_rowIndex][i];
            }
        }

        private DataTable _results;

        public DataTable Table 
        { 
            get 
            {
                if (_results == null)
                {
                    _results = new DataTable();
                }
                return _results; 
            } 
            set 
            {
                _results = value;
                for (int i = 0; i < _results.Columns.Count; i++)
                {
                    // SqlDataReader is case insensitive - MockDataReader should be too
                    string adjustedName = _results.Columns[i].ColumnName.ToLower(System.Globalization.CultureInfo.CurrentCulture);
                    if (!_adjustedColumnNameMap.ContainsKey(adjustedName))
                    {
                        _adjustedColumnNameMap.Add(adjustedName, i);
                        _nonAdjustedColumnNameMap.Add(_results.Columns[i].ColumnName, i);
                    }
                }
            } 
        }

        Dictionary<string, int> _adjustedColumnNameMap = new Dictionary<string, int>();
        Dictionary<string, int> _nonAdjustedColumnNameMap = new Dictionary<string, int>();
        int _rowIndex = -1;
    }
}
