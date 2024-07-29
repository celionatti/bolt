# Explanation of Enhancements

## Attribute Casting

The castAttributes method ensures attributes are cast to the specified types before insert and update operations.

## Password Hashing

The setPasswordAttribute method hashes passwords using password_hash with the PASSWORD_BCRYPT algorithm.
Secure Password Handling: The save method ensures the password is hashed before saving.

## Exception Handling

The __set method throws an exception if an attribute is not fillable or is guarded, enhancing security and data integrity.
This ensures your User model leverages the enhanced capabilities of the DatabaseModel base class, handling casts, hidden attributes, validation, and security robustly.
